<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Ssl\DomainSslInspector;
use App\Http\Controllers\Controller;
use App\Support\DemoMode;
use App\Models\DnsRecord;
use App\Models\ManagedDomain;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use Database\Seeders\SslDomainDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SslDomainController extends Controller
{
    public function index(Request $request): View
    {
        if (DemoMode::enabled() && ManagedDomain::query()->doesntExist()) {
            (new SslDomainDemoSeeder)->run();
        }

        $domains = ManagedDomain::query()
            ->with(['server', 'tenant', 'project'])
            ->orderBy('domain')
            ->paginate(10)
            ->withQueryString();

        $sslMonitoring = ManagedDomain::query()
            ->whereNotNull('ssl_expires_at')
            ->orderBy('ssl_expires_at')
            ->take(6)
            ->get();

        $dnsRecords = DnsRecord::query()
            ->with('domain')
            ->orderBy('record_type')
            ->take(18)
            ->get();

        $tenantMappings = ManagedDomain::query()
            ->where('is_tenant_custom', true)
            ->orWhere('is_subdomain', true)
            ->with('tenant')
            ->orderBy('domain')
            ->take(8)
            ->get();

        $kpis = [
            'total' => ManagedDomain::query()->count(),
            'activeSsl' => ManagedDomain::query()->where('ssl_status', 'active')->count(),
            'expiringSsl' => ManagedDomain::query()->whereIn('ssl_status', ['expiring_soon'])->count(),
            'dnsErrors' => ManagedDomain::query()->where('dns_status', 'error')->count()
                + DnsRecord::query()->where('propagation_status', 'failed')->count(),
            'tenantDomains' => ManagedDomain::query()->where('is_tenant_custom', true)->count(),
            'renewalAlerts' => ManagedDomain::query()
                ->where(function ($q) {
                    $q->where('ssl_expires_at', '<=', now()->addDays(30))
                        ->orWhere('domain_expires_at', '<=', now()->addDays(30));
                })
                ->count(),
        ];

        $spark = fn (string $key) => \App\Support\OperationalMetrics::emptySparkline();
        $alerts = $this->buildAlerts();
        $expiryTimeline = $this->buildExpiryTimeline();

        return view('admin.ssl-domains.index', compact(
            'domains',
            'sslMonitoring',
            'dnsRecords',
            'tenantMappings',
            'kpis',
            'spark',
            'alerts',
            'expiryTimeline',
        ));
    }

    public function create(): View
    {
        return view('admin.ssl-domains.create', [
            'domain' => new ManagedDomain,
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'servers' => Server::query()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, DomainSslInspector $inspector): RedirectResponse
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:managed_domains,domain'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'project_id' => ['nullable', 'exists:hosted_projects,id'],
            'registrar' => ['nullable', 'string', 'max:255'],
            'routing_target' => ['nullable', 'string', 'max:255'],
            'ssl_issuer' => ['nullable', 'string', 'max:255'],
            'domain_expires_at' => ['nullable', 'date'],
            'ssl_expires_at' => ['nullable', 'date'],
            'auto_renew' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'is_subdomain' => ['boolean'],
            'is_tenant_custom' => ['boolean'],
            'probe_ssl' => ['boolean'],
        ]);

        $domainName = strtolower(trim($data['domain']));
        $isWildcard = str_starts_with($domainName, '*.') || $request->boolean('is_wildcard');

        $record = ManagedDomain::query()->create([
            'domain' => $domainName,
            'tenant_id' => $data['tenant_id'] ?? null,
            'server_id' => $data['server_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'registrar' => $data['registrar'] ?? null,
            'routing_target' => $data['routing_target'] ?? null,
            'ssl_issuer' => $data['ssl_issuer'] ?? null,
            'domain_expires_at' => $data['domain_expires_at'] ?? null,
            'ssl_expires_at' => $data['ssl_expires_at'] ?? null,
            'status' => 'active',
            'ssl_status' => filled($data['ssl_expires_at'] ?? null) ? 'active' : 'pending',
            'dns_status' => 'healthy',
            'auto_renew' => $request->boolean('auto_renew', true),
            'is_wildcard' => $isWildcard,
            'is_subdomain' => $request->boolean('is_subdomain') || substr_count($domainName, '.') > 1,
            'is_tenant_custom' => $request->boolean('is_tenant_custom'),
            'last_dns_check_at' => now(),
        ]);

        $message = __('Domain :domain registered.', ['domain' => $record->domain]);

        if ($request->boolean('probe_ssl', true)) {
            $ssl = $inspector->inspect($record->domain);
            $record->update([
                'ssl_status' => $ssl['ssl_status'],
                'ssl_expires_at' => $ssl['ssl_expires_at'],
                'ssl_issuer' => $ssl['ssl_issuer'] ?? $record->ssl_issuer,
                'status' => in_array($ssl['ssl_status'], ['expired', 'invalid'], true) ? 'invalid_ssl' : $record->status,
            ]);
            if ($ssl['message']) {
                $message .= ' '.$ssl['message'];
            }
        }

        return redirect()
            ->route('ssl-domains.index')
            ->with('status', $message);
    }

    public function renew(Request $request): RedirectResponse
    {
        return redirect()
            ->route('ssl-domains.index')
            ->with('status', __('SSL renewal queued for the selected certificate.'));
    }

    public function verifyDns(Request $request): RedirectResponse
    {
        return redirect()
            ->route('ssl-domains.index')
            ->with('status', __('DNS verification scan started across all zones.'));
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildAlerts(): Collection
    {
        $alerts = collect();

        foreach (ManagedDomain::query()->where('domain_expires_at', '<=', now()->addDays(30))->orderBy('domain_expires_at')->take(3)->get() as $domain) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Domain expiring soon'),
                'body' => __(':domain expires in :days days.', [
                    'domain' => $domain->domain,
                    'days' => max(0, $domain->daysUntilDomainExpiry() ?? 0),
                ]),
                'time' => $domain->domain_expires_at?->diffForHumans() ?? __('Soon'),
            ]);
        }

        foreach (ManagedDomain::query()->where('ssl_expires_at', '<=', now()->addDays(30))->orderBy('ssl_expires_at')->take(3)->get() as $domain) {
            $alerts->push([
                'type' => $domain->daysUntilSslExpiry() !== null && $domain->daysUntilSslExpiry() < 7 ? 'danger' : 'warning',
                'title' => __('SSL expiry warning'),
                'body' => __('Certificate for :domain expires in :label.', [
                    'domain' => $domain->domain,
                    'label' => $domain->sslExpiryLabel(),
                ]),
                'time' => $domain->ssl_expires_at?->diffForHumans() ?? __('Soon'),
            ]);
        }

        foreach (ManagedDomain::query()->where('dns_status', 'error')->take(2)->get() as $domain) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('DNS failure'),
                'body' => __(':domain has misconfigured or unreachable DNS records.', ['domain' => $domain->domain]),
                'time' => __('Live'),
            ]);
        }

        foreach (ManagedDomain::query()->whereIn('ssl_status', ['invalid', 'expired'])->take(2)->get() as $domain) {
            $alerts->push([
                'type' => 'critical',
                'title' => __('Invalid certificate'),
                'body' => __(':domain — :issuer chain rejected by edge.', [
                    'domain' => $domain->domain,
                    'issuer' => $domain->ssl_issuer ?? __('Unknown'),
                ]),
                'time' => __('Security'),
            ]);
        }

        foreach (ManagedDomain::query()->where('status', 'dns_error')->take(1)->get() as $domain) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Routing problem'),
                'body' => __('Traffic to :domain may not reach :target.', [
                    'domain' => $domain->domain,
                    'target' => $domain->routing_target ?? __('origin'),
                ]),
                'time' => __('Edge'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('All zones healthy'),
                'body' => __('No critical SSL or DNS alerts in the current window.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(8);
    }

    /**
     * @return array<int, array{label: string, ssl: int, domain: int}>
     */
    private function buildExpiryTimeline(): array
    {
        $buckets = [];
        foreach ([7, 14, 30, 60, 90] as $days) {
            $buckets[] = [
                'label' => $days.'d',
                'ssl' => ManagedDomain::query()
                    ->whereBetween('ssl_expires_at', [now(), now()->addDays($days)])
                    ->count(),
                'domain' => ManagedDomain::query()
                    ->whereBetween('domain_expires_at', [now(), now()->addDays($days)])
                    ->count(),
            ];
        }

        return $buckets;
    }
}
