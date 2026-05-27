<?php

namespace App\Domain\Rbac;

class PermissionRegistry
{
    /** @return list<array{code: string, name: string, description: string, group: string}> */
    public static function definitions(): array
    {
        return [
            ['code' => 'dashboard.view', 'name' => 'View dashboard', 'description' => 'Access the main operations overview.', 'group' => 'dashboard'],
            ['code' => 'tenants.view', 'name' => 'View tenants', 'description' => 'List and view tenant records.', 'group' => 'tenants'],
            ['code' => 'tenants.create', 'name' => 'Create tenants', 'description' => 'Provision new tenants.', 'group' => 'tenants'],
            ['code' => 'tenants.update', 'name' => 'Update tenants', 'description' => 'Edit tenant configuration.', 'group' => 'tenants'],
            ['code' => 'tenants.suspend', 'name' => 'Suspend tenants', 'description' => 'Suspend or restrict tenant access.', 'group' => 'tenants'],
            ['code' => 'projects.view', 'name' => 'View projects', 'description' => 'List hosted projects.', 'group' => 'projects'],
            ['code' => 'projects.update', 'name' => 'Update projects', 'description' => 'Modify project settings.', 'group' => 'projects'],
            ['code' => 'servers.view', 'name' => 'View servers', 'description' => 'List infrastructure servers.', 'group' => 'servers'],
            ['code' => 'servers.update', 'name' => 'Update servers', 'description' => 'Create or modify servers.', 'group' => 'servers'],
            ['code' => 'servers.sync', 'name' => 'Sync servers', 'description' => 'Trigger server telemetry sync.', 'group' => 'servers'],
            ['code' => 'billing.view', 'name' => 'View billing', 'description' => 'Access billing summaries and payments.', 'group' => 'billing'],
            ['code' => 'invoices.view', 'name' => 'View invoices', 'description' => 'List and view invoices.', 'group' => 'invoices'],
            ['code' => 'invoices.generate', 'name' => 'Generate invoices', 'description' => 'Create tenant invoices.', 'group' => 'invoices'],
            ['code' => 'invoices.mark_sent', 'name' => 'Mark invoices sent', 'description' => 'Mark invoices as sent to customers.', 'group' => 'invoices'],
            ['code' => 'invoices.record_payment', 'name' => 'Record invoice payments', 'description' => 'Record payments against invoices.', 'group' => 'invoices'],
            ['code' => 'invoices.cancel', 'name' => 'Cancel invoices', 'description' => 'Cancel issued invoices.', 'group' => 'invoices'],
            ['code' => 'hr.staff.view', 'name' => 'View staff', 'description' => 'View internal HR staff profiles.', 'group' => 'hr'],
            ['code' => 'hr.staff.create', 'name' => 'Create staff', 'description' => 'Create staff profiles.', 'group' => 'hr'],
            ['code' => 'hr.staff.update', 'name' => 'Update staff', 'description' => 'Update staff profiles.', 'group' => 'hr'],
            ['code' => 'support.tickets.view', 'name' => 'View support tickets', 'description' => 'View support ticket queues.', 'group' => 'support'],
            ['code' => 'support.tickets.assign', 'name' => 'Assign support tickets', 'description' => 'Assign tickets to staff.', 'group' => 'support'],
            ['code' => 'activity_logs.view', 'name' => 'View activity logs', 'description' => 'View system activity logs.', 'group' => 'system'],
            ['code' => 'risk_center.view', 'name' => 'View risk center', 'description' => 'Access operational risk center.', 'group' => 'system'],
            ['code' => 'system_settings.update', 'name' => 'Update system settings', 'description' => 'Change platform configuration.', 'group' => 'system'],
            ['code' => 'rbac.manage', 'name' => 'Manage access control', 'description' => 'Manage roles, permissions, and assignments.', 'group' => 'system'],
            ['code' => 'deployments.view', 'name' => 'View deployments', 'description' => 'View deployment history and status.', 'group' => 'deployments'],
            ['code' => 'deployments.create', 'name' => 'Create deployments', 'description' => 'Create deployment records.', 'group' => 'deployments'],
            ['code' => 'deployments.update', 'name' => 'Update deployments', 'description' => 'Approve, cancel, or rollback deployments.', 'group' => 'deployments'],
            ['code' => 'deployments.run', 'name' => 'Run deployments', 'description' => 'Trigger new deployments.', 'group' => 'deployments'],
            ['code' => 'monitoring.view', 'name' => 'View monitoring', 'description' => 'Access monitoring dashboards.', 'group' => 'monitoring'],
            ['code' => 'monitoring.sync', 'name' => 'Sync monitoring', 'description' => 'Trigger monitoring sync jobs.', 'group' => 'monitoring'],
            ['code' => 'backups.view', 'name' => 'View backups', 'description' => 'View backup schedules and runs.', 'group' => 'backups'],
            ['code' => 'backups.create', 'name' => 'Create backups', 'description' => 'Run on-demand backups.', 'group' => 'backups'],
            ['code' => 'backups.restore', 'name' => 'Restore backups', 'description' => 'Restore from backup.', 'group' => 'backups'],
            ['code' => 'ssl.view', 'name' => 'View SSL & domains', 'description' => 'View SSL certificates and domains.', 'group' => 'ssl'],
            ['code' => 'ssl.update', 'name' => 'Update SSL & domains', 'description' => 'Manage SSL and domain records.', 'group' => 'ssl'],
            ['code' => 'ssl.renew', 'name' => 'Renew SSL', 'description' => 'Trigger SSL renewal.', 'group' => 'ssl'],
            ['code' => 'subscriptions.view', 'name' => 'View subscriptions', 'description' => 'View SaaS subscription plans.', 'group' => 'subscriptions'],
            ['code' => 'subscriptions.update', 'name' => 'Update subscriptions', 'description' => 'Manage subscription plans.', 'group' => 'subscriptions'],
            ['code' => 'payments.view', 'name' => 'View payments', 'description' => 'View payment records.', 'group' => 'payments'],
            ['code' => 'payments.record', 'name' => 'Record payments', 'description' => 'Record or reconcile payments.', 'group' => 'payments'],
            ['code' => 'license_logs.view', 'name' => 'View license logs', 'description' => 'View license check logs.', 'group' => 'license'],
            ['code' => 'api_credentials.view', 'name' => 'View API credentials', 'description' => 'View API keys and webhooks.', 'group' => 'api'],
            ['code' => 'api_credentials.update', 'name' => 'Update API credentials', 'description' => 'Manage API keys and webhooks.', 'group' => 'api'],
            ['code' => 'payments_gateway.view', 'name' => 'View Payments Gateway', 'description' => 'Monitor and control payments.pradytecai.com.', 'group' => 'api'],
            ['code' => 'payments_gateway.manage', 'name' => 'Manage Payments Gateway', 'description' => 'Create, edit, and suspend gateway records.', 'group' => 'api'],
            ['code' => 'tenant_access_controls.view', 'name' => 'View tenant access controls', 'description' => 'View tenant enforcement policies.', 'group' => 'access'],
            ['code' => 'tenant_access_controls.update', 'name' => 'Update tenant access controls', 'description' => 'Suspend, restrict, or unlock tenants.', 'group' => 'access'],
            ['code' => 'server_health.view', 'name' => 'View server health', 'description' => 'View server health metrics.', 'group' => 'servers'],
            ['code' => 'server_health.sync', 'name' => 'Sync server health', 'description' => 'Refresh server health data.', 'group' => 'servers'],
            ['code' => 'provider_notices.view', 'name' => 'View provider notices', 'description' => 'View hosting provider notices.', 'group' => 'servers'],
            ['code' => 'provider_notices.update', 'name' => 'Update provider notices', 'description' => 'Edit provider notices.', 'group' => 'servers'],
            ['code' => 'provider_notices.resolve', 'name' => 'Resolve provider notices', 'description' => 'Resolve or dismiss provider notices.', 'group' => 'servers'],
        ];
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_column(self::definitions(), 'code');
    }
}
