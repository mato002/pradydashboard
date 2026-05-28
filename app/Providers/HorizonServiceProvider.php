<?php

namespace App\Providers;

use App\Domain\Rbac\RbacGuard;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::auth(function ($request) {
            $user = $request->user();

            return $user instanceof User
                && app(RbacGuard::class)->can($user, 'monitoring.view');
        });
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user = null) {
            if (! $user instanceof User) {
                return false;
            }

            return app(RbacGuard::class)->can($user, 'monitoring.view');
        });
    }
}
