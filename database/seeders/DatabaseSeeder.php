<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacBootstrapSeeder::class,
            LicenseModuleSeeder::class,
            HrDepartmentSeeder::class,
        ]);
        $this->call(DocumentTemplateSeeder::class);
        $this->call(PaymentTestDataSeeder::class);

        if (config('app.demo_mode')) {
            $this->call([
                BackupDemoSeeder::class,
                SslDomainDemoSeeder::class,
                SubscriptionDemoSeeder::class,
                InvoiceDemoSeeder::class,
                AccessControlDemoSeeder::class,
                DeploymentDemoSeeder::class,
            ]);
        }

    }
}
