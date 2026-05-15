<?php

namespace App\Providers;

use App\Domain\Tenancy\Repositories\EloquentTenantRepository;
use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use App\Observers\TenantObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('license-check', function (Request $request) {
            return Limit::perMinute(config('prady.license.rate_limit_per_minute', 120))
                ->by($request->ip().'|'.($request->bearerToken() ?? 'anon'));
        });
    }
}
