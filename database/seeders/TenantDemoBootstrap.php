<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDemoBootstrap extends Seeder
{
    public function run(): void
    {
        (new ServerHealthDemoSeeder)->run();
        (new ProductCatalogSeeder)->run();
        (new FleetLinkSeeder)->run();
        (new LicenseModuleSeeder)->run();
        (new SubscriptionDemoSeeder)->run();
    }
}
