<?php

namespace Database\Seeders;

use App\Models\HostedProject;
use App\Models\Product;
use App\Models\Server;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['name' => 'Prady MFI System', 'slug' => 'mfi', 'domains' => ['mfi.pradytecai.com', 'demo-mfi.pradytecai.com']],
            ['name' => 'Property Management System', 'slug' => 'property', 'domains' => ['property.pradytecai.com', 'demo-property.pradytecai.com']],
            ['name' => 'Prady CRM', 'slug' => 'crm', 'domains' => ['crm.pradytecai.com']],
            ['name' => 'SpareMe', 'slug' => 'spareme', 'domains' => ['spareme.co.ke', 'investor.spareme.co.ke', 'api.spareme.co.ke']],
            ['name' => 'ISP Management System', 'slug' => 'isp', 'domains' => ['isp.pradytecai.com']],
            ['name' => 'Jana Prints', 'slug' => 'jana-prints', 'domains' => ['jana.pradytecai.com']],
            ['name' => 'Prady Dashboard', 'slug' => 'dashboard', 'domains' => ['dashboard.pradytecai.com']],
        ];

        $server = Server::query()->first();

        foreach ($catalog as $item) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['name'].' — PradytecAI product',
                    'category' => 'saas',
                    'status' => 'active',
                    'default_billing_model' => 'subscription',
                    'default_license_mode' => 'module',
                ],
            );

            foreach ($item['domains'] as $domain) {
                HostedProject::query()->updateOrCreate(
                    ['domain' => $domain],
                    [
                        'product_id' => $product->id,
                        'server_id' => $server?->id,
                        'name' => $domain,
                        'base_url' => 'https://'.$domain,
                        'environment' => str_contains($domain, 'demo') ? 'demo' : 'production',
                        'product_key' => $product->slug,
                        'status' => 'active',
                    ],
                );
            }
        }
    }
}
