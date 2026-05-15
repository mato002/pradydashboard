<?php

use App\Http\Controllers\Admin\AccessControlsController;
use App\Http\Controllers\Admin\ActivityLogsController;
use App\Http\Controllers\Admin\ApiCredentialsController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeploymentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LicenseCheckLogController;
use App\Http\Controllers\Admin\ModulePlaceholderController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\ServerHealthController;
use App\Http\Controllers\Admin\SslDomainController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SupportTicketsController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UsersRolesController;
use App\Http\Controllers\Admin\TenantModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('servers/probe', [ServerController::class, 'probe'])->name('servers.probe');
    Route::post('servers/sync-fleet', [ServerController::class, 'syncFleet'])->name('servers.sync-fleet');
    Route::post('servers/{server}/sync-telemetry', [ServerController::class, 'syncTelemetry'])->name('servers.sync-telemetry');
    Route::resource('servers', ServerController::class);

    Route::post('projects/{project}/regenerate-token', [ProjectController::class, 'regenerateToken'])
        ->name('projects.regenerate-token');
    Route::resource('projects', ProjectController::class);

    Route::post('tenants/{tenant}/modules', [TenantModuleController::class, 'update'])
        ->name('tenants.modules.update');
    Route::resource('tenants', TenantController::class);

    Route::get('support-tickets', [SupportTicketsController::class, 'index'])->name('support-tickets.index');
    Route::get('support-tickets/create', [SupportTicketsController::class, 'create'])->name('support-tickets.create');
    Route::post('support-tickets', [SupportTicketsController::class, 'store'])->name('support-tickets.store');
    Route::get('support-tickets/{reference}', [SupportTicketsController::class, 'show'])->name('support-tickets.show')->where('reference', '[A-Za-z0-9\-]+');
    Route::get('support-tickets/{reference}/edit', [SupportTicketsController::class, 'edit'])->name('support-tickets.edit')->where('reference', '[A-Za-z0-9\-]+');
    Route::put('support-tickets/{reference}', [SupportTicketsController::class, 'update'])->name('support-tickets.update')->where('reference', '[A-Za-z0-9\-]+');
    Route::delete('support-tickets/{reference}', [SupportTicketsController::class, 'destroy'])->name('support-tickets.destroy')->where('reference', '[A-Za-z0-9\-]+');

    Route::get('api-credentials', [ApiCredentialsController::class, 'index'])->name('api-credentials.index');
    Route::prefix('api-credentials')->name('api-credentials.')->group(function () {
        Route::get('keys/create', [ApiCredentialsController::class, 'createKey'])->name('keys.create');
        Route::post('keys', [ApiCredentialsController::class, 'storeKey'])->name('keys.store');
        Route::get('keys/{key}', [ApiCredentialsController::class, 'showKey'])->name('keys.show')->where('key', '[A-Za-z0-9_]+');
        Route::get('keys/{key}/edit', [ApiCredentialsController::class, 'editKey'])->name('keys.edit')->where('key', '[A-Za-z0-9_]+');
        Route::put('keys/{key}', [ApiCredentialsController::class, 'updateKey'])->name('keys.update')->where('key', '[A-Za-z0-9_]+');
        Route::delete('keys/{key}', [ApiCredentialsController::class, 'destroyKey'])->name('keys.destroy')->where('key', '[A-Za-z0-9_]+');
        Route::get('webhooks/create', [ApiCredentialsController::class, 'createWebhook'])->name('webhooks.create');
        Route::post('webhooks', [ApiCredentialsController::class, 'storeWebhook'])->name('webhooks.store');
        Route::get('webhooks/{webhook}', [ApiCredentialsController::class, 'showWebhook'])->name('webhooks.show')->where('webhook', '[A-Za-z0-9_]+');
        Route::get('webhooks/{webhook}/edit', [ApiCredentialsController::class, 'editWebhook'])->name('webhooks.edit')->where('webhook', '[A-Za-z0-9_]+');
        Route::put('webhooks/{webhook}', [ApiCredentialsController::class, 'updateWebhook'])->name('webhooks.update')->where('webhook', '[A-Za-z0-9_]+');
    });

    Route::get('users-roles', [UsersRolesController::class, 'index'])->name('users-roles.index');
    Route::prefix('users-roles')->name('users-roles.')->group(function () {
        Route::get('users/create', [UsersRolesController::class, 'createUser'])->name('users.create');
        Route::post('users', [UsersRolesController::class, 'storeUser'])->name('users.store');
        Route::get('users/{userRef}', [UsersRolesController::class, 'showUser'])->name('users.show')->where('userRef', '[A-Za-z0-9_]+');
        Route::get('users/{userRef}/edit', [UsersRolesController::class, 'editUser'])->name('users.edit')->where('userRef', '[A-Za-z0-9_]+');
        Route::put('users/{userRef}', [UsersRolesController::class, 'updateUser'])->name('users.update')->where('userRef', '[A-Za-z0-9_]+');
        Route::delete('users/{userRef}', [UsersRolesController::class, 'destroyUser'])->name('users.destroy')->where('userRef', '[A-Za-z0-9_]+');
        Route::get('roles/create', [UsersRolesController::class, 'createRole'])->name('roles.create');
        Route::post('roles', [UsersRolesController::class, 'storeRole'])->name('roles.store');
        Route::get('roles/{slug}', [UsersRolesController::class, 'showRole'])->name('roles.show')->where('slug', '[a-z0-9_]+');
        Route::get('roles/{slug}/edit', [UsersRolesController::class, 'editRole'])->name('roles.edit')->where('slug', '[a-z0-9_]+');
        Route::put('roles/{slug}', [UsersRolesController::class, 'updateRole'])->name('roles.update')->where('slug', '[a-z0-9_]+');
    });

    Route::get('system-settings/export', [SystemSettingsController::class, 'export'])->name('system-settings.export');
    Route::post('system-settings/restore-defaults', [SystemSettingsController::class, 'restoreDefaults'])->name('system-settings.restore-defaults');
    Route::get('system-settings', [SystemSettingsController::class, 'edit'])->name('system-settings.edit');
    Route::put('system-settings', [SystemSettingsController::class, 'update'])->name('system-settings.update');

    Route::get('license-logs', [LicenseCheckLogController::class, 'index'])->name('license-logs.index');

    Route::get('activity-logs', [ActivityLogsController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/export', [ActivityLogsController::class, 'export'])->name('activity-logs.export');

    Route::get('access-controls', [AccessControlsController::class, 'index'])->name('access-controls.index');
    Route::post('access-controls/policies', [AccessControlsController::class, 'store'])->name('access-controls.policies.store');
    Route::post('access-controls/tenants/{tenant}/suspend', [AccessControlsController::class, 'suspend'])->name('access-controls.suspend');
    Route::post('access-controls/tenants/{tenant}/grace', [AccessControlsController::class, 'grace'])->name('access-controls.grace');
    Route::post('access-controls/tenants/{tenant}/unlock', [AccessControlsController::class, 'unlock'])->name('access-controls.unlock');
    Route::post('access-controls/tenants/{tenant}/restrict', [AccessControlsController::class, 'restrict'])->name('access-controls.restrict');

    Route::get('server-health', [ServerHealthController::class, 'index'])->name('server-health.index');

    Route::get('deployments', [DeploymentController::class, 'index'])->name('deployments.index');
    Route::post('deployments/deploy', [DeploymentController::class, 'deploy'])->name('deployments.deploy');
    Route::post('deployments/{deployment}/rollback', [DeploymentController::class, 'rollback'])->name('deployments.rollback');
    Route::post('deployments/{deployment}/redeploy', [DeploymentController::class, 'redeploy'])->name('deployments.redeploy');
    Route::post('deployments/{deployment}/approve', [DeploymentController::class, 'approve'])->name('deployments.approve');
    Route::post('deployments/{deployment}/cancel', [DeploymentController::class, 'cancel'])->name('deployments.cancel');

    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups/run', [BackupController::class, 'run'])->name('backups.run');
    Route::patch('backups/schedules/{schedule}/toggle', [BackupController::class, 'toggleSchedule'])->name('backups.schedules.toggle');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/reconcile', [PaymentController::class, 'reconcile'])->name('payments.reconcile');
    Route::post('payments/retry-failed', [PaymentController::class, 'retryFailed'])->name('payments.retry-failed');

    Route::get('ssl-domains', [SslDomainController::class, 'index'])->name('ssl-domains.index');
    Route::get('ssl-domains/create', [SslDomainController::class, 'create'])->name('ssl-domains.create');
    Route::post('ssl-domains', [SslDomainController::class, 'store'])->name('ssl-domains.store');
    Route::post('ssl-domains/renew', [SslDomainController::class, 'renew'])->name('ssl-domains.renew');
    Route::post('ssl-domains/verify-dns', [SslDomainController::class, 'verifyDns'])->name('ssl-domains.verify-dns');

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('subscriptions/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('subscriptions/invoice', [SubscriptionController::class, 'generateInvoice'])->name('subscriptions.invoice');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::post('invoices/reminders', [InvoiceController::class, 'sendReminders'])->name('invoices.reminders');
    Route::patch('invoices/schedules/{schedule}/toggle', [InvoiceController::class, 'toggleSchedule'])->name('invoices.schedules.toggle');

    Route::get('modules/{section}', ModulePlaceholderController::class)
        ->whereIn('section', [
            'reports',
            'settings',
        ])
        ->name('modules.placeholder');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
