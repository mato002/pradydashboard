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
use App\Http\Controllers\Admin\PaymentReconciliationController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\ServerHealthController;
use App\Http\Controllers\Admin\ServerProviderNoticeController;
use App\Http\Controllers\Admin\SslDomainController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SupportTicketCommentController;
use App\Http\Controllers\Admin\SupportTicketsController;
use App\Http\Controllers\Admin\TenantCommunicationController;
use App\Http\Controllers\Admin\TenantNoticeController;
use App\Http\Controllers\Admin\TenantSupportTicketController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\ProjectModuleController;
use App\Http\Controllers\Admin\ProjectVersionController;
use App\Http\Controllers\Admin\RiskCenterController;
use App\Http\Controllers\Admin\TenantBillingController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\OperationalDocumentController;
use App\Http\Controllers\Admin\TenantProjectIntegrationController;
use App\Http\Controllers\Admin\TenantProjectLicenseController;
use App\Http\Controllers\Admin\TenantProjectInfrastructureController;
use App\Http\Controllers\Admin\TenantProjectModuleSubscriptionController;
use App\Http\Controllers\Admin\TenantProjectSubscriptionController;
use App\Http\Controllers\Admin\TenantProjectVersionController;
use App\Http\Controllers\Admin\HrController;
use App\Http\Controllers\Admin\HrDepartmentController;
use App\Http\Controllers\Admin\StaffAssignmentController;
use App\Http\Controllers\Admin\StaffDocumentController;
use App\Http\Controllers\Admin\StaffProfileController;
use App\Http\Controllers\Admin\UsersRolesController;
use App\Http\Controllers\Admin\TenantModuleController;
use App\Http\Controllers\ActiveRoleController;
use App\Http\Controllers\Admin\Rbac\PermissionController as RbacPermissionController;
use App\Http\Controllers\Admin\Rbac\RoleController as RbacRoleController;
use App\Http\Controllers\Admin\Rbac\RoleInheritanceController;
use App\Http\Controllers\Admin\Rbac\RolePermissionController;
use App\Http\Controllers\Admin\Rbac\RoleSwitchLogController;
use App\Http\Controllers\Admin\Rbac\UserRoleAssignmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::post('active-role/switch', [ActiveRoleController::class, 'switch'])->name('active-role.switch');

    Route::middleware('permission:servers.sync')->group(function () {
    Route::post('servers/probe', [ServerController::class, 'probe'])->name('servers.probe');
    Route::post('servers/sync-fleet', [ServerController::class, 'syncFleet'])->name('servers.sync-fleet');
    Route::post('servers/{server}/sync-telemetry', [ServerController::class, 'syncTelemetry'])->name('servers.sync-telemetry');
    });

    Route::resource('servers', ServerController::class)
        ->middlewareFor(['index', 'show'], 'permission:servers.view')
        ->middlewareFor(['create', 'store'], 'permission:servers.update')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:servers.update');
    Route::post('servers/{server}/notices', [ServerProviderNoticeController::class, 'store'])
        ->middleware('permission:provider_notices.update')
        ->name('servers.notices.store');
    Route::put('servers/{server}/notices/{notice}', [ServerProviderNoticeController::class, 'update'])
        ->middleware('permission:provider_notices.update')
        ->name('servers.notices.update');
    Route::delete('servers/{server}/notices/{notice}', [ServerProviderNoticeController::class, 'destroy'])
        ->middleware('permission:provider_notices.resolve')
        ->name('servers.notices.destroy');

    Route::post('projects/{project}/regenerate-token', [ProjectController::class, 'regenerateToken'])
        ->middleware('permission:projects.update')
        ->name('projects.regenerate-token');
    Route::resource('projects', ProjectController::class)
        ->middlewareFor(['index', 'show'], 'permission:projects.view')
        ->middlewareFor(['create', 'store'], 'permission:projects.update')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:projects.update');

    Route::post('tenants/{tenant}/modules', [TenantModuleController::class, 'update'])
        ->name('tenants.modules.update');
    Route::post('tenants/{tenant}/project-subscriptions', [TenantProjectSubscriptionController::class, 'store'])
        ->name('tenants.project-subscriptions.store');
    Route::put('tenants/{tenant}/project-subscriptions/{subscription}', [TenantProjectSubscriptionController::class, 'update'])
        ->name('tenants.project-subscriptions.update');
    Route::post('tenants/{tenant}/project-subscriptions/sync', [TenantProjectSubscriptionController::class, 'sync'])
        ->name('tenants.project-subscriptions.sync');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/license/activate', [TenantProjectLicenseController::class, 'activate'])
        ->name('tenants.project-subscriptions.license.activate');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/license/suspend', [TenantProjectLicenseController::class, 'suspend'])
        ->name('tenants.project-subscriptions.license.suspend');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/license/disable', [TenantProjectLicenseController::class, 'disable'])
        ->name('tenants.project-subscriptions.license.disable');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/license/grace', [TenantProjectLicenseController::class, 'extendGrace'])
        ->name('tenants.project-subscriptions.license.grace');
    Route::post('tenants/{tenant}/documents', [OperationalDocumentController::class, 'store'])
        ->name('tenants.documents.store');
    Route::put('tenants/{tenant}/documents/{document}', [OperationalDocumentController::class, 'update'])
        ->name('tenants.documents.update');
    Route::delete('tenants/{tenant}/documents/{document}', [OperationalDocumentController::class, 'destroy'])
        ->name('tenants.documents.destroy');
    Route::get('tenants/{tenant}/documents/{document}/download', [OperationalDocumentController::class, 'download'])
        ->name('tenants.documents.download');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations', [TenantProjectIntegrationController::class, 'store'])
        ->name('tenants.project-subscriptions.integrations.store');
    Route::put('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}', [TenantProjectIntegrationController::class, 'update'])
        ->name('tenants.project-subscriptions.integrations.update');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}/test', [TenantProjectIntegrationController::class, 'test'])
        ->name('tenants.project-subscriptions.integrations.test');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}/pull-system-info', [TenantProjectIntegrationController::class, 'pullSystemInfo'])
        ->name('tenants.project-subscriptions.integrations.pull-system-info');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}/pull-version', [TenantProjectIntegrationController::class, 'pullVersion'])
        ->name('tenants.project-subscriptions.integrations.pull-version');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}/pull-usage', [TenantProjectIntegrationController::class, 'pullUsage'])
        ->name('tenants.project-subscriptions.integrations.pull-usage');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/integrations/{integration}/heartbeat', [TenantProjectIntegrationController::class, 'heartbeat'])
        ->name('tenants.project-subscriptions.integrations.heartbeat');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/modules', [TenantProjectModuleSubscriptionController::class, 'update'])
        ->name('tenants.project-subscriptions.modules.update');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/infrastructure', [TenantProjectInfrastructureController::class, 'update'])
        ->name('tenants.project-subscriptions.infrastructure.update');
    Route::post('tenants/{tenant}/project-subscriptions/{subscription}/version', [TenantProjectVersionController::class, 'update'])
        ->name('tenants.project-subscriptions.version.update');
    Route::post('tenants/{tenant}/support-tickets', [TenantSupportTicketController::class, 'store'])
        ->name('tenants.support-tickets.store');
    Route::put('tenants/{tenant}/support-tickets/{ticket}', [TenantSupportTicketController::class, 'update'])
        ->name('tenants.support-tickets.update');
    Route::post('tenants/{tenant}/support-tickets/{ticket}/resolve', [TenantSupportTicketController::class, 'resolve'])
        ->name('tenants.support-tickets.resolve');
    Route::post('tenants/{tenant}/support-tickets/{ticket}/comments', [SupportTicketCommentController::class, 'storeForTenant'])
        ->name('tenants.support-tickets.comments.store');
    Route::post('tenants/{tenant}/communications', [TenantCommunicationController::class, 'store'])
        ->name('tenants.communications.store');
    Route::patch('tenants/{tenant}/communications/{communication}/status', [TenantCommunicationController::class, 'updateStatus'])
        ->name('tenants.communications.status');
    Route::post('tenants/{tenant}/notices', [TenantNoticeController::class, 'store'])
        ->name('tenants.notices.store');
    Route::post('tenants/{tenant}/notices/{notice}/send', [TenantNoticeController::class, 'markSent'])
        ->name('tenants.notices.send');
    Route::put('tenants/{tenant}/billing-profile', [TenantBillingController::class, 'updateProfile'])
        ->name('tenants.billing-profile.update');
    Route::post('tenants/{tenant}/billing/generate-draft', [TenantBillingController::class, 'generateDraft'])
        ->name('tenants.billing.generate-draft');
    Route::resource('tenants', TenantController::class)
        ->middlewareFor(['index', 'show'], 'permission:tenants.view')
        ->middlewareFor(['create', 'store'], 'permission:tenants.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:tenants.update');

    Route::middleware('permission:projects.update')->group(function () {
    Route::post('projects/{project}/modules', [ProjectModuleController::class, 'store'])->name('projects.modules.store');
    Route::delete('projects/{project}/modules/{module}', [ProjectModuleController::class, 'destroy'])->name('projects.modules.destroy');
    Route::post('projects/{project}/versions', [ProjectVersionController::class, 'store'])->name('projects.versions.store');
    Route::delete('projects/{project}/versions/{version}', [ProjectVersionController::class, 'destroy'])->name('projects.versions.destroy');
    });

    Route::get('support-tickets', [SupportTicketsController::class, 'index'])->middleware('permission:support.tickets.view')->name('support-tickets.index');
    Route::get('support-tickets/create', [SupportTicketsController::class, 'create'])->middleware('permission:support.tickets.view')->name('support-tickets.create');
    Route::post('support-tickets', [SupportTicketsController::class, 'store'])->middleware('permission:support.tickets.assign')->name('support-tickets.store');
    Route::get('support-tickets/{reference}', [SupportTicketsController::class, 'show'])->middleware('permission:support.tickets.view')->name('support-tickets.show')->where('reference', '[A-Za-z0-9\-]+');
    Route::get('support-tickets/{reference}/edit', [SupportTicketsController::class, 'edit'])->middleware('permission:support.tickets.view')->name('support-tickets.edit')->where('reference', '[A-Za-z0-9\-]+');
    Route::put('support-tickets/{reference}', [SupportTicketsController::class, 'update'])->middleware('permission:support.tickets.assign')->name('support-tickets.update')->where('reference', '[A-Za-z0-9\-]+');
    Route::delete('support-tickets/{reference}', [SupportTicketsController::class, 'destroy'])->middleware('permission:support.tickets.assign')->name('support-tickets.destroy')->where('reference', '[A-Za-z0-9\-]+');
    Route::post('support-tickets/{ticket}/comments', [SupportTicketCommentController::class, 'storeGlobal'])
        ->middleware('permission:support.tickets.assign')
        ->name('support-tickets.comments.store')
        ->where('ticket', '[0-9]+');

    Route::get('api-credentials', [ApiCredentialsController::class, 'index'])->middleware('permission:api_credentials.view')->name('api-credentials.index');
    Route::prefix('api-credentials')->name('api-credentials.')->middleware('permission:api_credentials.update')->group(function () {
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

    Route::get('hr', [HrController::class, 'index'])->middleware('permission:hr.staff.view')->name('hr.index');
    Route::resource('hr/departments', HrDepartmentController::class)->names('hr.departments')->except(['show', 'destroy'])
        ->middlewareFor('index', 'permission:hr.staff.view')
        ->middlewareFor(['create', 'store'], 'permission:hr.staff.create')
        ->middlewareFor(['edit', 'update'], 'permission:hr.staff.update');
    Route::resource('hr/staff', StaffProfileController::class)->names('hr.staff')->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'permission:hr.staff.view')
        ->middlewareFor(['create', 'store'], 'permission:hr.staff.create')
        ->middlewareFor(['edit', 'update'], 'permission:hr.staff.update');
    Route::middleware('permission:hr.staff.update')->group(function () {
    Route::post('hr/staff/{staff}/assignments', [StaffAssignmentController::class, 'store'])->name('hr.staff.assignments.store');
    Route::put('hr/staff/{staff}/assignments/{assignment}', [StaffAssignmentController::class, 'update'])->name('hr.staff.assignments.update');
    Route::delete('hr/staff/{staff}/assignments/{assignment}', [StaffAssignmentController::class, 'destroy'])->name('hr.staff.assignments.destroy');
    Route::post('hr/staff/{staff}/documents', [StaffDocumentController::class, 'store'])->name('hr.staff.documents.store');
    Route::delete('hr/staff/{staff}/documents/{document}', [StaffDocumentController::class, 'destroy'])->name('hr.staff.documents.destroy');
    });
    Route::get('hr/staff/{staff}/documents/{document}/download', [StaffDocumentController::class, 'download'])->middleware('permission:hr.staff.view')->name('hr.staff.documents.download');

    Route::middleware('permission:rbac.manage')->group(function () {
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
    });

    Route::prefix('access-control')->name('access-control.')->middleware('permission:rbac.manage')->group(function () {
        Route::get('permissions', [RbacPermissionController::class, 'index'])->name('permissions.index');
        Route::get('roles', [RbacRoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RbacRoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RbacRoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}', [RbacRoleController::class, 'show'])->name('roles.show');
        Route::get('roles/{role}/edit', [RbacRoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RbacRoleController::class, 'update'])->name('roles.update');
        Route::get('roles/{role}/permissions', [RolePermissionController::class, 'edit'])->name('roles.permissions.edit');
        Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');
        Route::get('roles/{role}/inheritance', [RoleInheritanceController::class, 'edit'])->name('roles.inheritance.edit');
        Route::put('roles/{role}/inheritance', [RoleInheritanceController::class, 'update'])->name('roles.inheritance.update');
        Route::get('assignments', [UserRoleAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [UserRoleAssignmentController::class, 'store'])->name('assignments.store');
        Route::post('assignments/{assignment}/revoke', [UserRoleAssignmentController::class, 'revoke'])->name('assignments.revoke');
        Route::get('switch-logs', [RoleSwitchLogController::class, 'index'])->name('switch-logs.index');
    });

    Route::get('system-settings/export', [SystemSettingsController::class, 'export'])->middleware('permission:system_settings.update')->name('system-settings.export');
    Route::post('system-settings/restore-defaults', [SystemSettingsController::class, 'restoreDefaults'])->middleware('permission:system_settings.update')->name('system-settings.restore-defaults');
    Route::get('system-settings', [SystemSettingsController::class, 'edit'])->middleware('permission:system_settings.update')->name('system-settings.edit');
    Route::put('system-settings', [SystemSettingsController::class, 'update'])->middleware('permission:system_settings.update')->name('system-settings.update');

    Route::get('license-logs', [LicenseCheckLogController::class, 'index'])->middleware('permission:license_logs.view')->name('license-logs.index');

    Route::get('activity-logs', [ActivityLogsController::class, 'index'])->middleware('permission:activity_logs.view')->name('activity-logs.index');
    Route::get('activity-logs/export', [ActivityLogsController::class, 'export'])->middleware('permission:activity_logs.view')->name('activity-logs.export');

    Route::get('risk-center', [RiskCenterController::class, 'index'])->middleware('permission:risk_center.view')->name('risk-center.index');
    Route::post('risk-center/acknowledge', [RiskCenterController::class, 'acknowledge'])->middleware('permission:risk_center.view')->name('risk-center.acknowledge');
    Route::delete('risk-center/acknowledge/{riskKey}', [RiskCenterController::class, 'unacknowledge'])->middleware('permission:risk_center.view')->name('risk-center.unacknowledge');

    Route::get('access-controls', [AccessControlsController::class, 'index'])->middleware('permission:tenant_access_controls.view')->name('access-controls.index');
    Route::post('access-controls/policies', [AccessControlsController::class, 'store'])->middleware('permission:tenant_access_controls.update')->name('access-controls.policies.store');
    Route::post('access-controls/tenants/{tenant}/suspend', [AccessControlsController::class, 'suspend'])->middleware('permission:tenant_access_controls.update')->name('access-controls.suspend');
    Route::post('access-controls/tenants/{tenant}/grace', [AccessControlsController::class, 'grace'])->middleware('permission:tenant_access_controls.update')->name('access-controls.grace');
    Route::post('access-controls/tenants/{tenant}/unlock', [AccessControlsController::class, 'unlock'])->middleware('permission:tenant_access_controls.update')->name('access-controls.unlock');
    Route::post('access-controls/tenants/{tenant}/restrict', [AccessControlsController::class, 'restrict'])->middleware('permission:tenant_access_controls.update')->name('access-controls.restrict');

    Route::get('server-health', [ServerHealthController::class, 'index'])->middleware('permission:server_health.view')->name('server-health.index');

    Route::get('deployments', [DeploymentController::class, 'index'])->middleware('permission:deployments.view')->name('deployments.index');
    Route::post('deployments/deploy', [DeploymentController::class, 'deploy'])->middleware('permission:deployments.run')->name('deployments.deploy');
    Route::post('deployments/{deployment}/rollback', [DeploymentController::class, 'rollback'])->middleware('permission:deployments.update')->name('deployments.rollback');
    Route::post('deployments/{deployment}/redeploy', [DeploymentController::class, 'redeploy'])->middleware('permission:deployments.run')->name('deployments.redeploy');
    Route::post('deployments/{deployment}/approve', [DeploymentController::class, 'approve'])->middleware('permission:deployments.update')->name('deployments.approve');
    Route::post('deployments/{deployment}/cancel', [DeploymentController::class, 'cancel'])->middleware('permission:deployments.update')->name('deployments.cancel');

    Route::get('monitoring', [MonitoringController::class, 'index'])->middleware('permission:monitoring.view')->name('monitoring.index');

    Route::get('backups', [BackupController::class, 'index'])->middleware('permission:backups.view')->name('backups.index');
    Route::post('backups/run', [BackupController::class, 'run'])->middleware('permission:backups.create')->name('backups.run');
    Route::patch('backups/schedules/{schedule}/toggle', [BackupController::class, 'toggleSchedule'])->middleware('permission:backups.create')->name('backups.schedules.toggle');

    Route::get('payments', [PaymentController::class, 'index'])->middleware('permission:payments.view')->name('payments.index');
    Route::post('payments/reconcile', [PaymentController::class, 'reconcile'])->middleware('permission:payments.record')->name('payments.reconcile');
    Route::post('payments/retry-failed', [PaymentController::class, 'retryFailed'])->middleware('permission:payments.record')->name('payments.retry-failed');

    Route::get('ssl-domains', [SslDomainController::class, 'index'])->middleware('permission:ssl.view')->name('ssl-domains.index');
    Route::get('ssl-domains/create', [SslDomainController::class, 'create'])->middleware('permission:ssl.update')->name('ssl-domains.create');
    Route::post('ssl-domains', [SslDomainController::class, 'store'])->middleware('permission:ssl.update')->name('ssl-domains.store');
    Route::post('ssl-domains/renew', [SslDomainController::class, 'renew'])->middleware('permission:ssl.renew')->name('ssl-domains.renew');
    Route::post('ssl-domains/verify-dns', [SslDomainController::class, 'verifyDns'])->middleware('permission:ssl.update')->name('ssl-domains.verify-dns');

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->middleware('permission:subscriptions.view')->name('subscriptions.index');
    Route::get('subscriptions/create', [SubscriptionController::class, 'create'])->middleware('permission:subscriptions.update')->name('subscriptions.create');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->middleware('permission:subscriptions.update')->name('subscriptions.store');
    Route::post('subscriptions/renew', [SubscriptionController::class, 'renew'])->middleware('permission:subscriptions.update')->name('subscriptions.renew');
    Route::post('subscriptions/invoice', [SubscriptionController::class, 'generateInvoice'])->middleware('permission:subscriptions.update')->name('subscriptions.invoice');

    Route::get('invoices', [InvoiceController::class, 'index'])->middleware('permission:invoices.view')->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->middleware('permission:invoices.generate')->name('invoices.create');
    Route::post('invoices/manual', [InvoiceController::class, 'store'])->middleware('permission:invoices.generate')->name('invoices.manual.store');
    Route::post('invoices/payments/record', [PaymentReconciliationController::class, 'store'])->middleware('permission:invoices.record_payment')->name('invoices.payments.record');
    Route::get('invoices/payments/{payment}/suggestions', [PaymentReconciliationController::class, 'suggestions'])->middleware('permission:invoices.view')->name('invoices.payments.suggestions');
    Route::post('invoices/payments/{payment}/match', [PaymentReconciliationController::class, 'match'])->middleware('permission:invoices.record_payment')->name('invoices.payments.match');
    Route::post('invoices/payments/{payment}/split', [PaymentReconciliationController::class, 'split'])->middleware('permission:invoices.record_payment')->name('invoices.payments.split');
    Route::post('invoices/payments/{payment}/duplicate', [PaymentReconciliationController::class, 'duplicate'])->middleware('permission:invoices.record_payment')->name('invoices.payments.duplicate');
    Route::post('invoices/payments/{payment}/ignore', [PaymentReconciliationController::class, 'ignore'])->middleware('permission:invoices.record_payment')->name('invoices.payments.ignore');
    Route::post('invoices/payments/{payment}/reverse', [PaymentReconciliationController::class, 'reverse'])->middleware('permission:invoices.record_payment')->name('invoices.payments.reverse');
    Route::get('invoices/tenants/{tenant}/billing-profile', [InvoiceController::class, 'tenantBillingProfile'])->middleware('permission:invoices.view')->name('invoices.tenants.billing-profile');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->middleware('permission:invoices.generate')->name('invoices.generate');
    Route::post('invoices/reminders', [InvoiceController::class, 'sendReminders'])->middleware('permission:invoices.mark_sent')->name('invoices.reminders');
    Route::post('invoices/{invoice}/reminder', [InvoiceController::class, 'sendInvoiceReminder'])->middleware('permission:invoices.mark_sent')->name('invoices.reminder');
    Route::post('invoices/{invoice}/collection-notes', [InvoiceController::class, 'storeCollectionNote'])->middleware('permission:invoices.mark_sent')->name('invoices.collection-notes.store');
    Route::post('invoices/{invoice}/collection-notes/{note}/complete', [InvoiceController::class, 'completeCollectionFollowUp'])->middleware('permission:invoices.mark_sent')->name('invoices.collection-notes.complete');
    Route::post('invoices/{invoice}/disputed', [InvoiceController::class, 'markInvoiceDisputed'])->middleware('permission:invoices.mark_sent')->name('invoices.disputed');
    Route::post('invoices/{invoice}/promise-to-pay', [InvoiceController::class, 'recordPromiseToPay'])->middleware('permission:invoices.mark_sent')->name('invoices.promise-to-pay');
    Route::post('invoices/{invoice}/escalate', [InvoiceController::class, 'escalateInvoice'])->middleware('permission:invoices.mark_sent')->name('invoices.escalate');
    Route::put('invoices/automation-rules', [InvoiceController::class, 'updateAutomationRules'])->middleware('permission:invoices.generate')->name('invoices.automation.update');
    Route::patch('invoices/schedules/{schedule}/toggle', [InvoiceController::class, 'toggleSchedule'])->middleware('permission:invoices.generate')->name('invoices.schedules.toggle');
    Route::get('invoices/templates/{documentTemplate}/sample-preview', [InvoiceController::class, 'previewDocumentTemplate'])->middleware('permission:invoices.view')->name('invoices.templates.sample-preview');
    Route::patch('invoices/templates/{template}', [InvoiceController::class, 'updateTemplate'])->middleware('permission:invoices.generate')->name('invoices.templates.update');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('permission:invoices.view')->name('invoices.show');
    Route::get('invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->middleware('permission:invoices.view')->name('invoices.preview');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->middleware('permission:invoices.view')->name('invoices.pdf');
    Route::post('invoices/{invoice}/finalize', [InvoiceController::class, 'finalizeDocument'])->middleware('permission:invoices.mark_sent')->name('invoices.finalize');
    Route::post('invoices/{invoice}/email', [InvoiceController::class, 'emailDocument'])->middleware('permission:invoices.mark_sent')->name('invoices.email');
    Route::post('invoices/{invoice}/regenerate', [InvoiceController::class, 'regeneratePdf'])->middleware('permission:invoices.generate')->name('invoices.regenerate');
    Route::post('invoices/{invoice}/approve-quotation', [InvoiceController::class, 'approveQuotation'])->middleware('permission:invoices.generate')->name('invoices.quotations.approve');
    Route::post('invoices/{invoice}/convert-quotation', [InvoiceController::class, 'convertQuotation'])->middleware('permission:invoices.generate')->name('invoices.quotations.convert');
    Route::post('invoices/{invoice}/convert-proforma', [InvoiceController::class, 'convertProforma'])->middleware('permission:invoices.generate')->name('invoices.proforma.convert');
    Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->middleware('permission:invoices.mark_sent')->name('invoices.mark-sent');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->middleware('permission:invoices.cancel')->name('invoices.cancel');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->middleware('permission:invoices.record_payment')->name('invoices.payments.store');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->middleware('permission:invoices.record_payment')->name('invoices.mark-paid');

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
