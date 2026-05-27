<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\ApiIntegrationsHub;
use App\Domain\Tenancy\Support\TenantSystemContractPresenter;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiCredentialsController extends Controller
{
    public function __construct(
        private readonly ApiIntegrationsHub $hub,
        private readonly TenantSystemContractPresenter $tenantSystemContract,
    ) {}

    public function index(): View
    {
        $summary = $this->hub->globalSummary();
        $providerIntegrations = $this->hub->providerIntegrations();
        $tenantSystemApis = $this->hub->tenantSystemApis();
        $projectApiKeys = $this->hub->projectApiKeys();
        $recentChecks = $this->hub->recentCheckLog();

        $kpis = [
            'total_configured' => [
                'value' => $summary['total_configured'],
                'sublabel' => __(':active active · :failing failing', [
                    'active' => $summary['active'],
                    'failing' => $summary['failing'],
                ]),
                'tone' => 'indigo',
            ],
            'active_apis' => [
                'value' => $summary['active'],
                'sublabel' => __('Provider :p · Tenant :t', [
                    'p' => $summary['provider_count'],
                    't' => $summary['tenant_system_count'],
                ]),
                'tone' => 'emerald',
            ],
            'failing_apis' => [
                'value' => $summary['failing'],
                'sublabel' => __(':n untested', ['n' => $summary['untested']]),
                'tone' => 'rose',
            ],
            'requests_today' => [
                'value' => $summary['requests_today'],
                'sublabel' => $summary['success_rate'] !== null
                    ? __('Success rate :rate%', ['rate' => $summary['success_rate']])
                    : __('No checks recorded yet'),
                'tone' => 'sky',
            ],
            'avg_response' => [
                'value' => $summary['average_response_time_ms'] !== null
                    ? $summary['average_response_time_ms'].'ms'
                    : '—',
                'sublabel' => __(':n failed checks', ['n' => $summary['failed_checks']]),
                'tone' => 'amber',
            ],
            'project_keys' => [
                'value' => $projectApiKeys->count(),
                'sublabel' => __('Prady-issued project keys'),
                'tone' => 'violet',
            ],
        ];

        $developer = $this->buildDeveloper();
        $tenantSystemContractDocs = $this->tenantSystemContract->forDocumentation();

        return view('admin.api-credentials.index', compact(
            'kpis',
            'summary',
            'providerIntegrations',
            'tenantSystemApis',
            'projectApiKeys',
            'recentChecks',
            'developer',
            'tenantSystemContractDocs',
        ));
    }

    public function createKey(): View
    {
        return view('admin.api-credentials.keys.create', [
            'key' => $this->blankKey(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeKey(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:hosted_projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,revoked'],
        ]);

        $project = Project::query()->findOrFail($data['project_id']);
        $project->update([
            'api_token' => Str::random(64),
            'status' => $data['status'] === 'active' ? 'active' : $project->status,
        ]);

        return redirect()
            ->route('api-credentials.keys.show', 'key_'.$project->id)
            ->with('status', __('API key provisioned for :project.', ['project' => $project->name]));
    }

    public function showKey(string $key): View
    {
        $profile = $this->resolveKey($key);

        return view('admin.api-credentials.keys.show', compact('profile', 'key'));
    }

    public function editKey(string $key): View
    {
        $profile = $this->resolveKey($key);

        return view('admin.api-credentials.keys.edit', [
            'profile' => $profile,
            'key' => $key,
            'isDemo' => false,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateKey(Request $request, string $key): RedirectResponse
    {
        $profile = $this->resolveKey($key);
        $projectId = $profile['project_id'] ?? null;

        if (! $projectId) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,revoked'],
            'regenerate' => ['nullable', 'boolean'],
        ]);

        $project = Project::query()->findOrFail($projectId);
        $updates = ['status' => $data['status'] === 'active' ? 'active' : ($data['status'] === 'suspended' ? 'maintenance' : $project->status)];

        if ($request->boolean('regenerate')) {
            $updates['api_token'] = Str::random(64);
        }

        $project->update($updates);

        return redirect()
            ->route('api-credentials.keys.show', 'key_'.$project->id)
            ->with('status', __('API key updated.'));
    }

    public function destroyKey(string $key): RedirectResponse
    {
        $profile = $this->resolveKey($key);
        $project = Project::query()->find($profile['project_id'] ?? 0);

        if ($project) {
            $project->update(['api_token' => null]);
        }

        return redirect()->route('api-credentials.index')->with('status', __('API key revoked.'));
    }

    public function createWebhook(): View
    {
        return view('admin.api-credentials.webhooks.create', [
            'webhook' => $this->blankWebhook(),
        ]);
    }

    public function storeWebhook(Request $request): RedirectResponse
    {
        $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'max:100'],
            'status' => ['required', 'in:active,paused,degraded'],
        ]);

        return redirect()
            ->route('api-credentials.index', ['tab' => 'webhooks'])
            ->with('status', __('Webhook registration is not persisted yet. Configure tenant endpoints first.'));
    }

    public function showWebhook(string $webhook): View
    {
        return redirect()->route('api-credentials.index', ['tab' => 'webhooks']);
    }

    public function editWebhook(string $webhook): View
    {
        return redirect()->route('api-credentials.index', ['tab' => 'webhooks']);
    }

    public function updateWebhook(Request $request, string $webhook): RedirectResponse
    {
        $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'max:100'],
            'status' => ['required', 'in:active,paused,degraded,failed'],
        ]);

        return redirect()
            ->route('api-credentials.index', ['tab' => 'webhooks'])
            ->with('status', __('Webhook configuration is not persisted yet.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveKey(string $key): array
    {
        if (! str_starts_with($key, 'key_')) {
            abort(404);
        }

        $projectId = (int) str_replace('key_', '', $key);
        $project = Project::query()->withCount('tenants')->findOrFail($projectId);

        if (! filled($project->api_token)) {
            abort(404);
        }

        $token = $project->api_token;
        $masked = substr($token, 0, 8).'…'.substr($token, -4);

        return [
            'id' => 'key_'.$project->id,
            'project_id' => $project->id,
            'project' => $project->name,
            'tenant' => $project->tenants_count > 0 ? $project->tenants_count.' '.__('tenants') : __('All tenants'),
            'name' => $project->name.' '.__('License API'),
            'permissions' => 'license:read, tenants:read',
            'masked_token' => 'prady_live_'.$masked,
            'full_token' => $token,
            'last_used' => $project->updated_at?->diffForHumans() ?? __('Never'),
            'status' => $project->status === 'active' ? 'active' : 'suspended',
            'expiry' => '—',
            'rate_limit' => '—',
            'created' => $project->created_at?->format('M j, Y') ?? '—',
            'scopes' => ['license:read', 'tenants:read'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blankKey(): array
    {
        return [
            'name' => '',
            'project_id' => null,
            'status' => 'active',
            'scopes' => ['license:read', 'tenants:read'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blankWebhook(): array
    {
        return [
            'id' => null,
            'url' => '',
            'events' => ['license.updated'],
            'status' => 'active',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDeveloper(): array
    {
        $base = rtrim((string) config('app.url'), '/').'/api';

        return [
            'base_url' => $base,
            'endpoints' => [
                ['method' => 'POST', 'path' => '/api/v1/license/check', 'desc' => __('Validate tenant license')],
                ['method' => 'POST', 'path' => '/api/v1/tenant/usage', 'desc' => __('Tenant usage heartbeat')],
                ['method' => 'GET', 'path' => '/api/system/info', 'desc' => __('Tenant system info (implemented on each product install)')],
            ],
        ];
    }
}
