<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LicenseModuleSeeder::class,
            HrDepartmentSeeder::class,
        ]);

        User::query()->firstOrCreate(
            ['email' => config('superuser.email')],
            [
                'name' => config('superuser.name'),
                'password' => Hash::make(config('superuser.password')),
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        $this->call(RbacBootstrapSeeder::class);
        $this->call(DocumentTemplateSeeder::class);

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
