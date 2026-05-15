<?php

namespace Database\Seeders;

use App\Models\LicenseModule;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantAccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccessControlDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (TenantAccessControl::query()->exists()) {
            return;
        }

        if (Tenant::query()->doesntExist()) {
            $this->bootstrapTenants();
        }

        if (Tenant::query()->doesntExist()) {
            return;
        }

        $modules = LicenseModule::query()->pluck('key')->take(4)->all();

        $templates = [
            ['level' => 'soft_reminder', 'restrict_login' => false, 'disabled' => [], 'trigger' => 'renewal_approaching'],
            ['level' => 'warning', 'restrict_login' => false, 'disabled' => [], 'trigger' => 'grace_period'],
            ['level' => 'restricted', 'restrict_login' => false, 'disabled' => ['reports', 'api'], 'trigger' => 'overdue_invoice'],
            ['level' => 'restricted', 'restrict_login' => true, 'disabled' => ['pos', 'inventory'], 'trigger' => 'policy_violation'],
            ['level' => 'suspended', 'restrict_login' => true, 'disabled' => $modules, 'trigger' => 'billing_enforcement'],
            ['level' => 'suspended', 'restrict_login' => true, 'disabled' => $modules, 'trigger' => 'manual_ops'],
            ['level' => 'warning', 'restrict_login' => false, 'disabled' => ['api'], 'trigger' => 'failed_logins'],
            ['level' => 'soft_reminder', 'restrict_login' => false, 'disabled' => [], 'trigger' => 'automated_warning'],
        ];

        $tenants = Tenant::query()->orderBy('company_name')->get();

        foreach ($tenants as $i => $tenant) {
            $tpl = $templates[$i % count($templates)];
            $status = $tenant->status;

            if ($status === 'suspended') {
                $tpl = $templates[4];
            } elseif ($status === 'overdue') {
                $tpl = $templates[2];
            } elseif ($status === 'trial' && $tenant->renewal_date?->isPast()) {
                $tpl = $templates[1];
            }

            TenantAccessControl::query()->create([
                'tenant_id' => $tenant->id,
                'level' => $tpl['level'],
                'restrict_login' => $tpl['restrict_login'],
                'disabled_modules' => $tpl['disabled'],
                'effective_from' => now()->subDays(random_int(1, 14)),
                'effective_until' => in_array($tpl['level'], ['soft_reminder', 'warning'], true)
                    ? now()->addDays(random_int(3, 21))
                    : null,
                'notes' => __('Trigger: :trigger', ['trigger' => str_replace('_', ' ', $tpl['trigger'])]),
            ]);

            if ($i % 3 === 0) {
                TenantAccessControl::query()->create([
                    'tenant_id' => $tenant->id,
                    'level' => 'soft_reminder',
                    'restrict_login' => false,
                    'disabled_modules' => [],
                    'effective_from' => now()->subDays(30),
                    'effective_until' => now()->subDays(20),
                    'notes' => __('Historical policy — superseded'),
                ]);
            }
        }
    }

    private function bootstrapTenants(): void
    {
        (new ServerHealthDemoSeeder)->run();

        $server = Server::query()->first();
        $project = Project::query()->first();

        if (! $project && $server) {
            $project = Project::query()->create([
                'server_id' => $server->id,
                'name' => 'Prady Core Platform',
                'domain' => 'core.prady.local',
                'status' => 'active',
                'api_token' => Str::random(64),
            ]);
        }

        if (! $project) {
            return;
        }

        (new LicenseModuleSeeder)->run();
        (new SubscriptionDemoSeeder)->run();
    }
}
