<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModulePlaceholderController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\TenantModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('servers', ServerController::class);

    Route::post('projects/{project}/regenerate-token', [ProjectController::class, 'regenerateToken'])
        ->name('projects.regenerate-token');
    Route::resource('projects', ProjectController::class);

    Route::post('tenants/{tenant}/modules', [TenantModuleController::class, 'update'])
        ->name('tenants.modules.update');
    Route::resource('tenants', TenantController::class);

    Route::get('modules/{section}', ModulePlaceholderController::class)
        ->whereIn('section', [
            'subscriptions',
            'invoices',
            'payments',
            'access-controls',
            'server-health',
            'backups',
            'ssl-domains',
            'deployments',
            'monitoring',
            'activity-logs',
            'support-tickets',
            'users-roles',
            'system-settings',
            'api-credentials',
            'reports',
            'settings',
        ])
        ->name('modules.placeholder');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
