<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiCredentialsController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->withCount('tenants')
            ->orderBy('name')
            ->get();

        $apiKeys = $this->buildApiKeys($projects);
        $webhooks = $this->buildWebhooks();
        $security = $this->buildSecurity();
        $analytics = $this->buildAnalytics();
        $developer = $this->buildDeveloper();
        $tokenDetail = $this->buildTokenDetails($apiKeys);

        $activeKeys = collect($apiKeys)->where('status', 'active')->count();
        $failedRequests = collect($analytics['error_endpoints'])->sum('errors') ?: 127;
        $rateViolations = collect($security['rate_limits'])->sum('violations_today') ?: 23;

        $kpis = [
            'active_keys' => [
                'value' => $activeKeys,
                'trend' => '+3',
                'sublabel' => __('Rotations due').': <span class="font-semibold text-amber-600 dark:text-amber-300">2</span>',
                'tone' => 'indigo',
                'points' => $this->spark('keys'),
            ],
            'requests_today' => [
                'value' => '284K',
                'trend' => '+18%',
                'sublabel' => __('Peak').': <span class="font-semibold text-slate-800 dark:text-slate-100">14:00 UTC</span>',
                'tone' => 'sky',
                'points' => $this->spark('requests'),
                'animate' => false,
            ],
            'failed_requests' => [
                'value' => $failedRequests,
                'trend' => '-12%',
                'sublabel' => __('Error rate').': <span class="font-semibold text-rose-600 dark:text-rose-300">0.04%</span>',
                'tone' => 'rose',
                'points' => $this->spark('failed'),
            ],
            'webhook_deliveries' => [
                'value' => '1,842',
                'trend' => '+6%',
                'sublabel' => __('Success').': <span class="font-semibold text-emerald-600 dark:text-emerald-300">99.2%</span>',
                'tone' => 'emerald',
                'points' => $this->spark('webhooks'),
                'animate' => false,
            ],
            'rate_violations' => [
                'value' => $rateViolations,
                'trend' => $rateViolations > 20 ? '+5' : '-8%',
                'sublabel' => __('Throttled').': <span class="font-semibold text-amber-600 dark:text-amber-300">'.$rateViolations.'</span>',
                'tone' => 'amber',
                'points' => $this->spark('rate'),
            ],
            'active_integrations' => [
                'value' => collect($webhooks)->where('status', 'active')->count() + 8,
                'trend' => '+2',
                'sublabel' => __('OAuth apps').': <span class="font-semibold text-violet-600 dark:text-violet-300">4</span>',
                'tone' => 'violet',
                'points' => $this->spark('integrations'),
            ],
        ];

        return view('admin.api-credentials.index', compact(
            'kpis',
            'apiKeys',
            'webhooks',
            'security',
            'analytics',
            'developer',
            'tokenDetail',
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
            'project_id' => ['required', 'exists:projects,id'],
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
        $isDemo = str_starts_with($key, 'key_demo_');

        return view('admin.api-credentials.keys.edit', [
            'profile' => $profile,
            'key' => $key,
            'isDemo' => $isDemo,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateKey(Request $request, string $key): RedirectResponse
    {
        $profile = $this->resolveKey($key);

        if (str_starts_with($key, 'key_demo_')) {
            return redirect()
                ->route('api-credentials.keys.show', $key)
                ->with('status', __('Demo keys are read-only. Link a project to persist credentials.'));
        }

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
        if (str_starts_with($key, 'key_demo_')) {
            return redirect()->route('api-credentials.index')->with('status', __('Demo key removed from view.'));
        }

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
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'max:100'],
            'status' => ['required', 'in:active,paused,degraded'],
        ]);

        $id = 'wh_'.str_pad((string) random_int(10, 99), 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('api-credentials.webhooks.show', $id)
            ->with('status', __('Webhook endpoint registered.'))
            ->with('webhook_draft', array_merge($data, ['id' => $id]));
    }

    public function showWebhook(string $webhook): View
    {
        $profile = $this->resolveWebhook($webhook);

        return view('admin.api-credentials.webhooks.show', compact('profile', 'webhook'));
    }

    public function editWebhook(string $webhook): View
    {
        $profile = $this->resolveWebhook($webhook);

        return view('admin.api-credentials.webhooks.edit', compact('profile', 'webhook'));
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
            ->route('api-credentials.webhooks.show', $webhook)
            ->with('status', __('Webhook configuration saved.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveKey(string $key): array
    {
        $keys = $this->buildApiKeys(Project::query()->withCount('tenants')->orderBy('name')->get());
        $found = collect($keys)->firstWhere('id', $key);

        if (! $found) {
            abort(404);
        }

        return $found;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveWebhook(string $webhook): array
    {
        $draft = session('webhook_draft');
        if (is_array($draft) && ($draft['id'] ?? '') === $webhook) {
            return array_merge($this->blankWebhook(), $draft);
        }

        $found = collect($this->buildWebhooks())->firstWhere('id', $webhook);

        if (! $found) {
            abort(404);
        }

        return $found;
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
            'delivery_rate' => 100,
            'signature' => 'HMAC-SHA256',
        ];
    }

    /**
     * @param  Collection<int, Project>  $projects
     * @return array<int, array<string, mixed>>
     */
    private function buildApiKeys(Collection $projects): array
    {
        $demo = $this->demoApiKeys();

        if ($projects->isEmpty()) {
            return $demo;
        }

        $mapped = $projects->map(function (Project $project, int $i) use ($demo) {
            $fallback = $demo[$i % count($demo)];
            $token = $project->api_token ?? Str::random(64);
            $masked = substr($token, 0, 8).'…'.substr($token, -4);

            return [
                'id' => 'key_'.$project->id,
                'project_id' => $project->id,
                'project' => $project->name,
                'tenant' => $project->tenants_count > 0 ? $project->tenants_count.' '.__('tenants') : __('All tenants'),
                'name' => $project->name.' '.__('License API'),
                'permissions' => $fallback['permissions'],
                'masked_token' => 'prady_live_'.$masked,
                'full_token' => $token,
                'last_used' => $project->updated_at?->diffForHumans() ?? __('Never'),
                'status' => $project->status === 'active' ? 'active' : 'suspended',
                'expiry' => Carbon::now()->addMonths(6)->format('M j, Y'),
                'rate_limit' => $fallback['rate_limit'],
                'created' => $project->created_at?->format('M j, Y') ?? '—',
                'scopes' => $fallback['scopes'],
            ];
        })->all();

        if (count($mapped) < 6) {
            $mapped = array_merge($mapped, array_slice($demo, count($mapped), 6 - count($mapped)));
        }

        return $mapped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoApiKeys(): array
    {
        $rows = [
            ['project' => 'MFI Core Banking', 'tenant' => 'Savanna Retail', 'name' => 'Production License', 'permissions' => 'license:read, tenants:read', 'rate' => '5,000/min', 'status' => 'active', 'scopes' => ['license:read', 'tenants:read', 'webhooks:write']],
            ['project' => 'Property ERP', 'tenant' => 'Coast Hotels', 'name' => 'Tenant Sync API', 'permissions' => 'tenants:write, billing:read', 'rate' => '2,000/min', 'status' => 'active', 'scopes' => ['tenants:read', 'tenants:write', 'billing:read']],
            ['project' => 'ISP OSS', 'tenant' => 'UrbanPay Ltd', 'name' => 'Webhook Signing Key', 'permissions' => 'webhooks:sign', 'rate' => '500/min', 'status' => 'active', 'scopes' => ['webhooks:write', 'events:subscribe']],
            ['project' => 'CRM Suite', 'tenant' => 'Acme Logistics', 'name' => 'OAuth Client Secret', 'permissions' => 'oauth:full', 'rate' => '1,000/min', 'status' => 'active', 'scopes' => ['oauth', 'users:read', 'contacts:write']],
            ['project' => 'Analytics Hub', 'tenant' => 'Nairobi Med Group', 'name' => 'Read-only Analytics', 'permissions' => 'analytics:read', 'rate' => '10,000/min', 'status' => 'active', 'scopes' => ['analytics:read']],
            ['project' => 'Legacy Bridge', 'tenant' => 'TechFarm Africa', 'name' => 'Deprecated v1 Key', 'permissions' => 'license:read', 'rate' => '100/min', 'status' => 'revoked', 'scopes' => ['license:read']],
            ['project' => 'Payment Gateway', 'tenant' => 'All tenants', 'name' => 'PCI Scoped Token', 'permissions' => 'payments:charge', 'rate' => '3,000/min', 'status' => 'active', 'scopes' => ['payments:charge', 'payments:refund']],
            ['project' => 'Staging Sandbox', 'tenant' => '—', 'name' => 'Test Environment', 'permissions' => 'sandbox:*', 'rate' => '100/min', 'status' => 'suspended', 'scopes' => ['sandbox']],
            ['project' => 'HR Integration', 'tenant' => 'Coast Hotels', 'name' => 'SCIM Provisioner', 'permissions' => 'users:provision', 'rate' => '500/min', 'status' => 'expired', 'scopes' => ['users:write', 'groups:read']],
        ];

        return collect($rows)->map(function (array $row, int $i) {
            $token = Str::random(48);

            return [
                'id' => 'key_demo_'.($i + 1),
                'project_id' => null,
                'project' => $row['project'],
                'tenant' => $row['tenant'],
                'name' => $row['name'],
                'permissions' => $row['permissions'],
                'masked_token' => 'prady_live_'.substr($token, 0, 8).'…'.substr($token, -4),
                'full_token' => 'prady_live_'.$token,
                'last_used' => ['2m ago', '15m ago', '1h ago', '3h ago', 'Yesterday', '—', '5m ago', '—', '30d ago'][$i % 9],
                'status' => $row['status'],
                'expiry' => Carbon::now()->addMonths(12 - $i)->format('M j, Y'),
                'rate_limit' => $row['rate'],
                'created' => Carbon::now()->subMonths(12 - $i)->format('M j, Y'),
                'scopes' => $row['scopes'],
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWebhooks(): array
    {
        return [
            [
                'id' => 'wh_01',
                'url' => 'https://api.savanna-retail.co.ke/webhooks/prady',
                'events' => ['license.updated', 'tenant.suspended'],
                'status' => 'active',
                'delivery_rate' => 99.8,
                'last_delivery' => '2m ago',
                'failures_24h' => 1,
                'retries_pending' => 0,
                'signature' => 'HMAC-SHA256',
                'timeline' => [
                    ['time' => '14:32', 'status' => 'success', 'event' => 'license.updated', 'code' => 200],
                    ['time' => '14:28', 'status' => 'success', 'event' => 'tenant.created', 'code' => 200],
                    ['time' => '14:15', 'status' => 'retry', 'event' => 'billing.invoice', 'code' => 502],
                ],
            ],
            [
                'id' => 'wh_02',
                'url' => 'https://hooks.urbanpay.io/v1/prady-events',
                'events' => ['payment.received', 'subscription.renewed'],
                'status' => 'active',
                'delivery_rate' => 98.4,
                'last_delivery' => '8m ago',
                'failures_24h' => 3,
                'retries_pending' => 2,
                'signature' => 'HMAC-SHA256',
                'timeline' => [
                    ['time' => '14:24', 'status' => 'success', 'event' => 'payment.received', 'code' => 200],
                    ['time' => '14:20', 'status' => 'failed', 'event' => 'payment.received', 'code' => 504],
                    ['time' => '14:18', 'status' => 'retry', 'event' => 'payment.received', 'code' => 504],
                ],
            ],
            [
                'id' => 'wh_03',
                'url' => 'https://crm.acme-logistics.com/api/callbacks',
                'events' => ['tenant.created', 'deployment.completed'],
                'status' => 'degraded',
                'delivery_rate' => 94.1,
                'last_delivery' => '1h ago',
                'failures_24h' => 12,
                'retries_pending' => 5,
                'signature' => 'HMAC-SHA256',
                'timeline' => [
                    ['time' => '13:10', 'status' => 'failed', 'event' => 'deployment.completed', 'code' => 403],
                    ['time' => '12:55', 'status' => 'success', 'event' => 'tenant.created', 'code' => 200],
                ],
            ],
            [
                'id' => 'wh_04',
                'url' => 'https://internal.nairobi-med.internal/hooks',
                'events' => ['license.check'],
                'status' => 'paused',
                'delivery_rate' => 0,
                'last_delivery' => '3d ago',
                'failures_24h' => 0,
                'retries_pending' => 0,
                'signature' => 'HMAC-SHA256',
                'timeline' => [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSecurity(): array
    {
        return [
            'ip_whitelist' => [
                ['ip' => '197.237.0.0/24', 'label' => __('Nairobi office'), 'hits' => 12400],
                ['ip' => '10.0.0.0/8', 'label' => __('Internal VPC'), 'hits' => 89200],
                ['ip' => '52.74.128.0/24', 'label' => __('AWS NAT — production'), 'hits' => 45600],
                ['ip' => '203.0.113.42', 'label' => __('Partner VPN'), 'hits' => 890],
            ],
            'scopes' => [
                ['scope' => 'license:read', 'keys' => 12, 'risk' => 'low'],
                ['scope' => 'tenants:write', 'keys' => 4, 'risk' => 'high'],
                ['scope' => 'payments:charge', 'keys' => 2, 'risk' => 'critical'],
                ['scope' => 'webhooks:write', 'keys' => 8, 'risk' => 'medium'],
                ['scope' => 'oauth', 'keys' => 3, 'risk' => 'high'],
            ],
            'rate_limits' => [
                ['tier' => __('Standard'), 'limit' => '1,000/min', 'violations_today' => 8],
                ['tier' => __('Enterprise'), 'limit' => '10,000/min', 'violations_today' => 12],
                ['tier' => __('Burst'), 'limit' => '50,000/min', 'violations_today' => 3],
            ],
            'alerts' => [
                ['type' => 'warning', 'title' => __('Token rotation overdue'), 'body' => __('Legacy Bridge key expired 30 days ago — revoke recommended.')],
                ['type' => 'danger', 'title' => __('Suspicious IP cluster'), 'body' => __('47 failed auth attempts from 185.220.x.x in last hour.')],
                ['type' => 'info', 'title' => __('Rate limit spike'), 'body' => __('MFI Core Banking exceeded burst quota at 13:45 UTC.')],
            ],
            'rotation_reminders' => [
                ['key' => 'Legacy Bridge — Deprecated v1', 'due' => __('Overdue'), 'days' => -30],
                ['key' => 'Payment Gateway — PCI Scoped', 'due' => __('14 days'), 'days' => 14],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAnalytics(): array
    {
        return [
            'request_volume' => [
                ['label' => '00', 'value' => 8200],
                ['label' => '04', 'value' => 4100],
                ['label' => '08', 'value' => 28400],
                ['label' => '12', 'value' => 45200],
                ['label' => '16', 'value' => 52100],
                ['label' => '20', 'value' => 31800],
            ],
            'response_times' => [
                ['label' => 'p50', 'ms' => 42],
                ['label' => 'p95', 'ms' => 128],
                ['label' => 'p99', 'ms' => 340],
            ],
            'endpoints' => [
                ['path' => '/api/license/check', 'requests' => 142000, 'pct' => 50],
                ['path' => '/api/tenants/sync', 'requests' => 68000, 'pct' => 24],
                ['path' => '/api/webhooks/deliver', 'requests' => 42000, 'pct' => 15],
                ['path' => '/api/billing/invoices', 'requests' => 18000, 'pct' => 6],
                ['path' => '/api/oauth/token', 'requests' => 14000, 'pct' => 5],
            ],
            'error_rate' => 0.04,
            'error_endpoints' => [
                ['path' => '/api/license/check', 'errors' => 42],
                ['path' => '/api/webhooks/deliver', 'errors' => 38],
                ['path' => '/api/tenants/sync', 'errors' => 27],
            ],
            'integration_trend' => [
                ['name' => __('REST'), 'pct' => 72],
                ['name' => __('Webhooks'), 'pct' => 18],
                ['name' => __('OAuth'), 'pct' => 10],
            ],
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
            'snippets' => [
                'curl' => "curl -X POST '{$base}/license/check' \\\n  -H 'Authorization: Bearer YOUR_API_KEY' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\"tenant_key\":\"acme-001\",\"domain\":\"app.example.com\"}'",
                'php' => "\$response = Http::withToken(\$apiKey)\n    ->post('{$base}/license/check', [\n        'tenant_key' => 'acme-001',\n        'domain' => 'app.example.com',\n    ]);",
                'node' => "const res = await fetch('{$base}/license/check', {\n  method: 'POST',\n  headers: { Authorization: `Bearer \${apiKey}` },\n  body: JSON.stringify({ tenant_key: 'acme-001' }),\n});",
            ],
            'sdks' => [
                ['name' => 'PHP SDK', 'version' => 'v2.4.1', 'install' => 'composer require pradytec/sdk'],
                ['name' => 'Node.js SDK', 'version' => 'v1.8.0', 'install' => 'npm install @pradytec/sdk'],
                ['name' => 'Python SDK', 'version' => 'v1.2.3', 'install' => 'pip install pradytec'],
            ],
            'endpoints' => [
                ['method' => 'POST', 'path' => '/api/license/check', 'desc' => __('Validate tenant license')],
                ['method' => 'GET', 'path' => '/api/tenants/{id}', 'desc' => __('Fetch tenant metadata')],
                ['method' => 'POST', 'path' => '/api/webhooks', 'desc' => __('Register webhook endpoint')],
                ['method' => 'POST', 'path' => '/api/oauth/token', 'desc' => __('Exchange OAuth credentials')],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $apiKeys
     * @return array<string, array<string, mixed>>
     */
    private function buildTokenDetails(array $apiKeys): array
    {
        $details = [];
        foreach ($apiKeys as $key) {
            $details[$key['id']] = [
                'ip_history' => [
                    ['ip' => '197.237.12.44', 'time' => __('2m ago'), 'action' => __('license/check')],
                    ['ip' => '10.0.4.22', 'time' => __('1h ago'), 'action' => __('tenant sync')],
                    ['ip' => '52.74.128.91', 'time' => __('3h ago'), 'action' => __('webhook sign')],
                ],
                'payload_sample' => '{"event":"license.updated","tenant_id":"tn_8f2a","timestamp":"'.now()->toIso8601String().'"}',
            ];
        }

        return $details;
    }

    /**
     * @return array<int, float>
     */
    private function spark(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
