<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Servers\ServerTelemetrySyncService;
use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function __construct(
        private readonly ServerTelemetrySyncService $telemetrySync,
    ) {}

    public function index(): View
    {
        $servers = Server::query()
            ->withCount(['projects', 'tenants'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('admin.servers.create', [
            'server' => new Server,
            'fleet' => $this->fleetSummary(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['hosted_domains'] = $this->parseDomains($request->string('hosted_domains_text')->toString());
        $data['provisioning_meta'] = $this->extractProvisioningMeta($request);

        $server = Server::query()->create($data);
        $sync = $this->telemetrySync->sync($server);

        return redirect()
            ->route('servers.show', $server)
            ->with('status', $sync['ok']
                ? __('Server created and telemetry synced.')
                : __('Server created. Configure WHM/cloud credentials to enable live sync.'));
    }

    public function syncTelemetry(Server $server): RedirectResponse
    {
        $result = $this->telemetrySync->sync($server);

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
        $server->load(['projects', 'tenants']);

        return view('admin.servers.show', compact('server'));
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

        return redirect()->route('servers.show', $server)->with('status', 'Server updated.');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('servers.index')->with('status', 'Server removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function fleetSummary(): array
    {
        $servers = Server::query()->get();
        $online = $servers->where('status', 'online');
        $sslProtected = $servers->filter(fn (Server $s) => $this->isSslHealthy($s->ssl_status))->count();
        $withBackup = $servers->filter(fn (Server $s) => filled($s->backup_status) && ! str_contains(strtolower((string) $s->backup_status), 'fail'))->count();
        $totalCpu = (int) $servers->sum('cpu_cores');
        $avgDisk = round((float) $servers->avg('disk_usage_percent'), 1);
        $backupCoverage = $servers->isEmpty() ? 0 : (int) round(($withBackup / $servers->count()) * 100);

        $backupCount = Backup::query()->where('status', 'completed')->count();
        $uptime = $servers->isEmpty()
            ? 99.9
            : round(99.5 + min(0.49, $online->count() / max(1, $servers->count()) * 0.49), 2);

        return [
            'total' => $servers->count(),
            'healthy' => $online->count(),
            'ssl_protected' => $sslProtected,
            'backup_coverage' => $backupCoverage,
            'cpu_capacity' => $totalCpu,
            'avg_disk' => $avgDisk,
            'fleet_uptime' => $uptime,
            'backup_jobs' => $backupCount,
            'spark' => [
                'total' => $this->sparkSeries($servers->count(), 8),
                'healthy' => $this->sparkSeries($online->count(), 8),
                'ssl' => $this->sparkSeries($sslProtected, 8),
                'backup' => $this->sparkSeries($backupCoverage, 8),
                'cpu' => $this->sparkSeries($totalCpu, 8),
                'disk' => $this->sparkSeries((int) $avgDisk, 8),
                'uptime' => $this->sparkSeries((int) ($uptime * 10), 8),
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function sparkSeries(int $base, int $len): array
    {
        $points = [];
        for ($i = 0; $i < $len; $i++) {
            $points[] = max(0, $base + random_int(-2, 3) - ($len - $i));
        }

        return $points;
    }

    private function isSslHealthy(?string $status): bool
    {
        if (! $status) {
            return false;
        }

        $s = strtolower($status);

        return str_contains($s, 'valid') || str_contains($s, 'active') || str_contains($s, 'ok');
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
            'status' => ['required', 'in:online,offline,unknown'],
            'ssl_status' => ['nullable', 'string', 'max:255'],
            'backup_status' => ['nullable', 'string', 'max:255'],
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
            'hostname', 'private_ip', 'region', 'environment',
            'bandwidth_gbps', 'network_speed', 'operating_system', 'architecture',
            'ssh_port', 'ssh_username', 'api_endpoint', 'auth_method', 'api_token',
            'firewall_status', 'access_restrictions',
            'certificate_expiry', 'waf_enabled', 'security_scan_status', 'monitoring_enabled',
            'billing_cycle', 'provider_invoice_ref', 'tenant_allocation', 'usage_threshold',
            'deployment_strategy', 'ci_cd_enabled', 'auto_backups', 'rollback_enabled',
            'monitoring_stack', 'notification_channels',
            'cloud_instance_id', 'whm_username', 'whm_port',
        ];

        $meta = [];
        foreach ($keys as $key) {
            if ($request->has("meta.$key")) {
                $meta[$key] = $request->input("meta.$key");
            }
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
