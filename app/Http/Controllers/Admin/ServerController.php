<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Activity\ActivityLogger;
use App\Domain\Hr\HrOverview;
use App\Support\ActivityLogCategory;
use App\Domain\Servers\FleetSummaryService;
use App\Domain\Servers\ServerTelemetrySyncService;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function __construct(
        private readonly ServerTelemetrySyncService $telemetrySync,
        private readonly FleetSummaryService $fleetSummary,
        private readonly HrOverview $hrOverview,
        private readonly ActivityLogger $activityLogger,
        private readonly ActivityLogQuery $activityQuery,
    ) {}

    public function index(): View
    {
        $scopeFilter = app(\App\Domain\Rbac\RbacScopeFilter::class);

        $servers = $scopeFilter
            ->applyServerScope(Server::query())
            ->withCount(['projects', 'tenants', 'openProviderNotices'])
            ->orderBy('name')
            ->paginate(15);

        $scopedServers = $scopeFilter->applyServerScope(Server::query());
        $allManualTelemetry = $scopedServers->exists()
            && ! (clone $scopedServers)->where('telemetry_mode', '!=', 'manual')->exists();

        return view('admin.servers.index', compact('servers', 'allManualTelemetry'));
    }

    public function create(): View
    {
        return view('admin.servers.create', [
            'server' => new Server([
                'telemetry_mode' => 'whm',
                'currency' => 'KES',
                'provider' => 'Hostinger',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['hosted_domains'] = $this->parseDomains($request->string('hosted_domains_text')->toString());

        $server = Server::query()->create($data);
        $server->mergeProvisioningMeta($this->extractProvisioningMeta($request));
        $server->save();

        $this->activityLogger->log(
            'server.created',
            ActivityLogCategory::SERVER,
            __('Server :name created', ['name' => $server->name]),
            $server,
        );

        $sync = $server->telemetry_mode === 'manual'
            ? ['ok' => false, 'message' => __('Server created with manual monitoring.')]
            : $this->telemetrySync->sync($server->fresh());

        return redirect()
            ->route('servers.show', $server)
            ->with('status', $sync['ok']
                ? __('Server created and telemetry synced.')
                : ($sync['message'] ?? __('Server created. Configure WHM API token to enable live metrics.')));
    }

    public function syncTelemetry(Server $server): RedirectResponse
    {
        $result = $this->telemetrySync->sync($server);
        $server->refresh();

        if (! $result['ok'] || in_array($server->status, ['offline', 'degraded', 'warning'], true)) {
            $this->activityLogger->log(
                'server.telemetry_warning',
                ActivityLogCategory::SERVER,
                $result['message'],
                $server,
                null,
                ['status' => $server->status, 'sync_status' => $server->sync_status],
            );
        }

        return redirect()
            ->back()
            ->with('status', $result['message']);
    }

    public function syncFleet(): RedirectResponse
    {
        $results = $this->telemetrySync->syncFleet();
        $ok = $results->where('ok', true)->count();

        return redirect()
            ->route('servers.index')
            ->with('status', __('Fleet sync complete: :ok/:total servers updated.', [
                'ok' => $ok,
                'total' => $results->count(),
            ]));
    }

    public function probe(Request $request): JsonResponse
    {
        $input = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'whm_cpanel_reference' => ['nullable', 'string'],
            'cpu_cores' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
        ]);

        return response()->json($this->telemetrySync->probe($input));
    }

    public function show(Server $server): View
    {
        $server->load([
            'projects',
            'tenants',
            'latestHealthLog',
            'providerNotices' => fn ($q) => $q->orderByDesc('notice_date')->orderByDesc('id'),
            'tenantProjectDeployments' => fn ($q) => $q->with([
                'subscription.tenant',
                'subscription.project',
                'subscription.versionTracking',
            ]),
        ]);

        $healthChecks = $server->meta('last_health_checks', []);

        return view('admin.servers.show', [
            'server' => $server,
            'healthChecks' => $healthChecks,
            'staffAssignments' => $this->hrOverview->assignmentsFor($server),
            'activityLogs' => $this->activityQuery->forContext(serverId: $server->id),
            'operationalRisks' => app(\App\Domain\Operations\OperationalRiskScanner::class)->forServer($server->id),
        ]);
    }

    public function edit(Server $server): View
    {
        return view('admin.servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $data = $this->validated($request);
        $data['hosted_domains'] = $this->parseDomains($request->string('hosted_domains_text')->toString());

        $server->update($data);
        $server->mergeProvisioningMeta($this->extractProvisioningMeta($request));
        $server->save();

        $changes = array_intersect_key($server->getChanges(), $data);
        unset($changes['updated_at']);
        if ($changes !== []) {
            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $server->getOriginal($key);
            }
            $this->activityLogger->log(
                'server.updated',
                ActivityLogCategory::SERVER,
                __('Server :name updated', ['name' => $server->name]),
                $server,
                $old,
                $changes,
            );
        }

        return redirect()->route('servers.show', $server)->with('status', __('Server updated.'));
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('servers.index')->with('status', __('Server removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'whm_cpanel_reference' => ['nullable', 'string'],
            'cpu_cores' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'ram_gb' => ['nullable', 'numeric', 'min:0'],
            'storage_gb' => ['nullable', 'numeric', 'min:0'],
            'disk_usage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:online,offline,warning,unknown'],
            'telemetry_mode' => ['required', 'in:manual,basic,whm'],
            'ssl_status' => ['nullable', 'string', 'max:255'],
            'backup_status' => ['nullable', 'string', 'max:255'],
            'billing_status' => ['nullable', 'string', 'max:64'],
            'renewal_expires_at' => ['nullable', 'date'],
            'monthly_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractProvisioningMeta(Request $request): array
    {
        $keys = [
            'hostname', 'private_ip', 'region', 'environment', 'operating_system',
            'ssh_port', 'ssh_username', 'api_endpoint', 'auth_method', 'api_token',
            'firewall_status', 'access_restrictions', 'whm_username', 'whm_port',
            'certificate_expiry', 'backup_policy', 'last_backup_date', 'monitoring_enabled',
            'bandwidth_gbps', 'bandwidth_used', 'architecture', 'network_speed',
            'waf_enabled', 'cloud_instance_id', 'hostinger_api_token', 'provider_api_token',
            'provider_account_ref', 'billing_cycle', 'provider_invoice_ref', 'billing_notes',
            'deployment_strategy', 'auto_backups', 'rollback_enabled', 'ci_cd_enabled',
        ];

        $tokenKeys = ['api_token', 'hostinger_api_token', 'provider_api_token'];
        $meta = [];

        foreach ($keys as $key) {
            if (! $request->has("meta.$key")) {
                continue;
            }

            $value = $request->input("meta.$key");

            if (in_array($key, $tokenKeys, true)
                && ($value === ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER || $value === '')) {
                continue;
            }

            $meta[$key] = $value;
        }

        return $meta;
    }

    /**
     * @return array<int, string>|null
     */
    private function parseDomains(string $text): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $domains = array_values(array_filter(array_map('trim', $lines)));

        return $domains === [] ? null : $domains;
    }
}
