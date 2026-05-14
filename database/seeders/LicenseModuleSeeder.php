<?php

namespace Database\Seeders;

use App\Models\LicenseModule;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class LicenseModuleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'accounting', 'label' => 'Accounting', 'description' => 'General ledger and journals', 'sort_order' => 10],
            ['key' => 'hr', 'label' => 'HR', 'description' => 'Human resources', 'sort_order' => 20],
            ['key' => 'payroll', 'label' => 'Payroll', 'description' => 'Pay runs and statutory', 'sort_order' => 30],
            ['key' => 'reports', 'label' => 'Reports', 'description' => 'Analytics and exports', 'sort_order' => 40],
            ['key' => 'crm', 'label' => 'CRM', 'description' => 'Contacts and pipeline', 'sort_order' => 50],
            ['key' => 'maintenance', 'label' => 'Maintenance', 'description' => 'Work orders and assets', 'sort_order' => 60],
            ['key' => 'billing', 'label' => 'Billing', 'description' => 'Invoicing inside tenant app', 'sort_order' => 70],
            ['key' => 'properties', 'label' => 'Properties', 'description' => 'Property portfolio core', 'sort_order' => 5],
        ];

        foreach ($rows as $row) {
            LicenseModule::query()->updateOrCreate(
                ['key' => $row['key']],
                $row
            );
        }

        $ids = LicenseModule::query()->orderBy('sort_order')->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        foreach (Tenant::query()->cursor() as $tenant) {
            if ($tenant->licenseModules()->count() > 0) {
                continue;
            }

            $tenant->licenseModules()->sync(
                $ids->mapWithKeys(fn (int $id): array => [$id => ['enabled' => true]])
            );
        }
    }
}
