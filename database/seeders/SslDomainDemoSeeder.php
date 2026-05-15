<?php

namespace Database\Seeders;

use App\Models\DnsRecord;
use App\Models\ManagedDomain;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SslDomainDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (ManagedDomain::query()->exists()) {
            return;
        }

        $servers = Server::query()->get();
        $tenants = Tenant::query()->get();
        $projects = Project::query()->get();

        $domains = [
            ['domain' => 'pradytecai.com', 'status' => 'active', 'ssl' => 'active', 'dns' => 'healthy', 'registrar' => 'Namecheap', 'wildcard' => false, 'tenant_custom' => false, 'ssl_days' => 89, 'domain_days' => 240, 'issuer' => "Let's Encrypt R3"],
            ['domain' => '*.pradytecai.com', 'status' => 'active', 'ssl' => 'active', 'dns' => 'healthy', 'registrar' => 'Cloudflare', 'wildcard' => true, 'tenant_custom' => false, 'ssl_days' => 62, 'domain_days' => 240, 'issuer' => "Let's Encrypt R3"],
            ['domain' => 'abc.property.pradytecai.com', 'status' => 'active', 'ssl' => 'active', 'dns' => 'healthy', 'registrar' => 'Internal', 'wildcard' => false, 'tenant_custom' => true, 'ssl_days' => 45, 'domain_days' => null, 'issuer' => "Let's Encrypt R3"],
            ['domain' => 'crm.clientdomain.com', 'status' => 'active', 'ssl' => 'expiring_soon', 'dns' => 'healthy', 'registrar' => 'GoDaddy', 'wildcard' => false, 'tenant_custom' => true, 'ssl_days' => 12, 'domain_days' => 180, 'issuer' => 'DigiCert'],
            ['domain' => 'api.edge.pradytecai.com', 'status' => 'active', 'ssl' => 'active', 'dns' => 'propagating', 'registrar' => 'Cloudflare', 'wildcard' => false, 'tenant_custom' => false, 'ssl_days' => 120, 'domain_days' => null, 'issuer' => "Let's Encrypt R3"],
            ['domain' => 'legacy.oldclient.co.ke', 'status' => 'expiring_soon', 'ssl' => 'expiring_soon', 'dns' => 'healthy', 'registrar' => 'Kenya Web', 'wildcard' => false, 'tenant_custom' => true, 'ssl_days' => 18, 'domain_days' => 22, 'issuer' => 'Sectigo'],
            ['domain' => 'shop.tenantnova.io', 'status' => 'dns_error', 'ssl' => 'invalid', 'dns' => 'error', 'registrar' => 'Namecheap', 'wildcard' => false, 'tenant_custom' => true, 'ssl_days' => -3, 'domain_days' => 90, 'issuer' => '—'],
            ['domain' => 'dashboard.hostinger-node.net', 'status' => 'active', 'ssl' => 'active', 'dns' => 'healthy', 'registrar' => 'Hostinger', 'wildcard' => false, 'tenant_custom' => false, 'ssl_days' => 200, 'domain_days' => 310, 'issuer' => "Let's Encrypt R3"],
            ['domain' => 'mail.acmecorp.com', 'status' => 'active', 'ssl' => 'active', 'dns' => 'healthy', 'registrar' => 'Google Domains', 'wildcard' => false, 'tenant_custom' => true, 'ssl_days' => 75, 'domain_days' => 150, 'issuer' => "Let's Encrypt R3"],
            ['domain' => 'staging.internal.prady', 'status' => 'invalid_ssl', 'ssl' => 'invalid', 'dns' => 'healthy', 'registrar' => 'Internal', 'wildcard' => false, 'tenant_custom' => false, 'ssl_days' => -14, 'domain_days' => null, 'issuer' => 'Self-signed'],
        ];

        foreach ($domains as $i => $d) {
            $server = $servers->get($i % max(1, $servers->count()));
            $tenant = $tenants->get($i % max(1, $tenants->count()));
            $project = $projects->get($i % max(1, $projects->count()));

            $domain = ManagedDomain::query()->create([
                'domain' => $d['domain'],
                'is_subdomain' => str_contains($d['domain'], '.') && ! str_starts_with($d['domain'], '*'),
                'tenant_id' => $d['tenant_custom'] ? $tenant?->id : null,
                'server_id' => $server?->id,
                'project_id' => $project?->id,
                'registrar' => $d['registrar'],
                'status' => $d['status'],
                'ssl_status' => $d['ssl'],
                'dns_status' => $d['dns'],
                'ssl_expires_at' => now()->addDays($d['ssl_days']),
                'domain_expires_at' => $d['domain_days'] ? now()->addDays($d['domain_days']) : null,
                'auto_renew' => $d['status'] !== 'dns_error',
                'is_wildcard' => $d['wildcard'],
                'is_tenant_custom' => $d['tenant_custom'],
                'ssl_issuer' => $d['issuer'],
                'routing_target' => 'edge-lb-'.(($i % 3) + 1),
                'certificate_chain' => ['ISRG Root X1', 'R3', $d['domain']],
                'renewal_history' => [
                    ['date' => now()->subMonths(3)->toDateString(), 'status' => 'success'],
                    ['date' => now()->subMonths(1)->toDateString(), 'status' => 'success'],
                ],
                'last_dns_check_at' => now()->subMinutes(random_int(5, 120)),
            ]);

            $this->seedDnsRecords($domain);
        }
    }

    private function seedDnsRecords(ManagedDomain $domain): void
    {
        $base = str_replace('*.', '', $domain->domain);
        $records = [
            ['A', '@', '185.199.108.'.random_int(10, 50), 'propagated'],
            ['A', 'www', '185.199.108.'.random_int(10, 50), $domain->dns_status === 'propagating' ? 'pending' : 'propagated'],
            ['CNAME', 'api', 'edge-lb.pradytecai.com', 'propagated'],
            ['TXT', '@', 'v=spf1 include:_spf.pradytecai.com ~all', 'propagated'],
            ['MX', '@', 'mail.'.explode('.', $base)[0].'.com', $domain->dns_status === 'error' ? 'failed' : 'propagated'],
            ['NS', '@', 'ns1.cloudflare.com', 'propagated'],
        ];

        foreach ($records as [$type, $host, $value, $prop]) {
            DnsRecord::query()->create([
                'managed_domain_id' => $domain->id,
                'record_type' => $type,
                'host' => $host,
                'value' => $value,
                'ttl' => $type === 'MX' ? 3600 : 300,
                'propagation_status' => $prop,
            ]);
        }
    }
}
