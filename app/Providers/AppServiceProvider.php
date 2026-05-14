<?php

namespace App\Providers;

use App\Domain\Tenancy\Repositories\EloquentTenantRepository;
use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use App\Observers\TenantObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantRepositoryInterface::class, EloquentTenantRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Tenant::observe(TenantObserver::class);
    }
}
