<?php

namespace App\Providers;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\RbacScopeFilter;
use App\Domain\Rbac\PermissionMatcher;
use App\Domain\Rbac\RbacGuard;
use App\Domain\Rbac\RoleInheritanceValidator;
use App\Domain\Rbac\RolePermissionResolver;
use App\Domain\Rbac\RoleSwitchService;
use App\Domain\Rbac\UserRoleAssignmentService;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Domain\Tenancy\Repositories\EloquentTenantRepository;
use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use App\Models\SupportTicket;
use App\Models\Server;
use App\Observers\OperationalCacheInvalidationObserver;
use App\Observers\TenantObserver;
use App\Support\Cache\OperationalCache;
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
        $this->app->singleton(OperationalCache::class);
        $this->app->singleton(TenantRepositoryInterface::class, EloquentTenantRepository::class);
        $this->app->singleton(ActivityLogger::class);
        $this->app->singleton(PermissionMatcher::class);
        $this->app->singleton(RolePermissionResolver::class);
        $this->app->singleton(RoleInheritanceValidator::class);
        $this->app->singleton(ActiveRoleService::class);
        $this->app->singleton(RbacScopeFilter::class);
        $this->app->singleton(RbacGuard::class);
        $this->app->singleton(RoleSwitchService::class);
        $this->app->singleton(UserRoleAssignmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Tenant::observe(TenantObserver::class);

        $cacheInvalidation = OperationalCacheInvalidationObserver::class;
        Tenant::observe($cacheInvalidation);
        TenantInvoice::observe($cacheInvalidation);
        TenantPayment::observe($cacheInvalidation);
        TenantProjectSubscription::observe($cacheInvalidation);
        SupportTicket::observe($cacheInvalidation);
        Server::observe($cacheInvalidation);

        RateLimiter::for('license-check', function (Request $request) {
            return Limit::perMinute(config('prady.license.rate_limit_per_minute', 120))
                ->by($request->ip().'|'.($request->bearerToken() ?? 'anon'));
        });

        Blade::if('permission', function (string $permission, array $scope = []) {
            $user = auth()->user();

            return $user instanceof User && app(RbacGuard::class)->can($user, $permission, $scope);
        });

        View::composer(['components.prady-shell', 'admin.partials.sidebar-nav'], function ($view) {
            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            $activeRoleService = app(ActiveRoleService::class);
            $view->with([
                'rbacActiveAssignment' => $activeRoleService->getActiveAssignment($user),
                'rbacActivatableAssignments' => $activeRoleService->activatableAssignments($user),
            ]);
        });
    }
}
