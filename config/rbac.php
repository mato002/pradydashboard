<?php

return [

    'super_admin_role_code' => env('RBAC_SUPER_ADMIN_CODE', 'super_admin'),

    'elevation_ttl_minutes' => (int) env('RBAC_ELEVATION_TTL', 15),

    'legacy_open_access' => (bool) env('RBAC_LEGACY_OPEN_ACCESS', false),

    /** Grant elevation timestamp when bootstrapping active Super Admin (first login). */
    'bootstrap_grant_elevation' => (bool) env('RBAC_BOOTSTRAP_GRANT_ELEVATION', true),

    /** Emails that receive bootstrap Super Admin (comma-separated in env). */
    'bootstrap_admin_emails' => array_filter(array_map(
        'trim',
        explode(',', (string) env('RBAC_BOOTSTRAP_ADMIN_EMAILS', 'superuser@pradytecai.com'))
    )),

    /** Role codes that cannot be created via admin UI. */
    'reserved_role_codes' => [
        'super_admin',
    ],

    /**
     * Permissions only grantable on global-scoped assignments.
     * Scoped roles are denied these even if attached to the role.
     */
    'global_only_permissions' => [
        'dashboard.',
        'rbac.',
        'system_settings.',
        'activity_logs.',
        'risk_center.',
        'hr.',
    ],

    /** Allowed wildcard prefixes when assigning to roles. */
    'wildcard_prefixes' => [
        'dashboard.',
        'tenants.',
        'projects.',
        'servers.',
        'billing.',
        'invoices.',
        'hr.',
        'support.',
        'activity_logs.',
        'risk_center.',
        'system_settings.',
        'rbac.',
        'deployments.',
        'monitoring.',
        'backups.',
        'ssl.',
        'subscriptions.',
        'payments.',
        'license_logs.',
        'api_credentials.',
        'payments_gateway.',
        'tenant_access_controls.',
        'server_health.',
        'provider_notices.',
    ],

    'permission_groups' => [
        'tenant' => ['tenants.', 'billing.', 'invoices.', 'support.tickets.'],
        'project' => ['projects.'],
        'server' => ['servers.'],
    ],

];
