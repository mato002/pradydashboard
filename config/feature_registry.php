<?php

return [

    'keyword_aliases' => [
        'invoice' => ['billing', 'financial', 'receipt'],
        'tenant' => ['client', 'customer', 'company'],
        'server' => ['infrastructure', 'host', 'hosting'],
        'payment' => ['mpesa', 'paybill', 'collections'],
        'support' => ['ticket', 'help'],
        'settings' => ['configuration', 'config'],
        'user' => ['role', 'permission', 'rbac'],
    ],

    'entries' => [
        ['label' => 'Overview', 'route' => 'dashboard', 'group' => 'Dashboard', 'permission' => 'dashboard.view', 'keywords' => ['home', 'overview']],

        ['label' => 'Servers', 'route' => 'servers.index', 'group' => 'Infrastructure', 'permission' => 'servers.view', 'keywords' => ['server', 'hosting']],
        ['label' => 'Hosted Projects', 'route' => 'hosted-projects.index', 'group' => 'Infrastructure', 'permission' => 'projects.view', 'keywords' => ['project', 'hosted']],
        ['label' => 'Products', 'route' => 'products.index', 'group' => 'Infrastructure', 'permission' => 'projects.view', 'keywords' => ['product', 'saas']],
        ['label' => 'SSL & Domains', 'route' => 'ssl-domains.index', 'group' => 'Infrastructure', 'permission' => 'ssl.view', 'keywords' => ['ssl', 'certificate', 'domain']],
        ['label' => 'Backups', 'route' => 'backups.index', 'group' => 'Infrastructure', 'permission' => 'backups.view', 'keywords' => ['backup', 'restore']],
        ['label' => 'Server Health', 'route' => 'server-health.index', 'group' => 'Infrastructure', 'permission' => 'server_health.view', 'keywords' => ['health', 'telemetry']],

        ['label' => 'All Tenants', 'route' => 'tenants.index', 'group' => 'Tenants', 'permission' => 'tenants.view', 'keywords' => ['tenant', 'directory']],
        ['label' => 'Subscriptions', 'route' => 'subscriptions.index', 'group' => 'Tenants', 'permission' => 'subscriptions.view', 'keywords' => ['subscription', 'license']],
        ['label' => 'License Logs', 'route' => 'license-logs.index', 'group' => 'Tenants', 'permission' => 'license_logs.view', 'keywords' => ['license', 'audit']],
        ['label' => 'Access Controls', 'route' => 'access-controls.index', 'group' => 'Tenants', 'permission' => 'tenant_access_controls.view', 'keywords' => ['access', 'suspend', 'grace']],

        ['label' => 'Financial Operations', 'route' => 'invoices.index', 'route_params' => ['tab' => 'overview'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['financial', 'billing', 'command center'], 'category' => 'financials'],
        ['label' => 'Invoices', 'route' => 'invoices.index', 'route_params' => ['tab' => 'invoices'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['invoice'], 'category' => 'financials'],
        ['label' => 'Quotations', 'route' => 'invoices.index', 'route_params' => ['tab' => 'quotations'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['quotation', 'quote'], 'category' => 'financials'],
        ['label' => 'Proforma', 'route' => 'invoices.index', 'route_params' => ['tab' => 'proforma'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['proforma'], 'category' => 'financials'],
        ['label' => 'Receipts', 'route' => 'invoices.index', 'route_params' => ['tab' => 'receipts'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['receipt'], 'category' => 'financials'],
        ['label' => 'Collections', 'route' => 'invoices.index', 'route_params' => ['tab' => 'collections'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['collections', 'overdue', 'debt'], 'category' => 'financials'],
        ['label' => 'Payment Inbox', 'route' => 'invoices.index', 'route_params' => ['tab' => 'payments'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['payment', 'reconcile'], 'category' => 'financials'],
        ['label' => 'Document Templates', 'route' => 'invoices.index', 'route_params' => ['tab' => 'templates'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['template', 'pdf'], 'category' => 'financials'],
        ['label' => 'Automation Rules', 'route' => 'invoices.index', 'route_params' => ['tab' => 'automation'], 'group' => 'Financials', 'permission' => 'invoices.view', 'keywords' => ['automation', 'billing cycle'], 'category' => 'financials'],
        ['label' => 'Payments', 'route' => 'payments.index', 'group' => 'Financials', 'permission' => 'payments.view', 'keywords' => ['payment', 'mpesa'], 'category' => 'financials'],
        ['label' => 'Create Invoice', 'route' => 'invoices.create', 'route_params' => ['type' => 'invoice'], 'group' => 'Financials', 'permission' => 'invoices.generate', 'keywords' => ['create', 'invoice', 'new'], 'category' => 'financials'],
        ['label' => 'Create Proforma', 'route' => 'invoices.create', 'route_params' => ['type' => 'proforma'], 'group' => 'Financials', 'permission' => 'invoices.generate', 'keywords' => ['create', 'proforma'], 'category' => 'financials'],
        ['label' => 'Create Quotation', 'route' => 'invoices.create', 'route_params' => ['type' => 'quotation'], 'group' => 'Financials', 'permission' => 'invoices.generate', 'keywords' => ['create', 'quotation'], 'category' => 'financials'],
        ['label' => 'Create Receipt', 'route' => 'invoices.create', 'route_params' => ['type' => 'receipt'], 'group' => 'Financials', 'permission' => 'invoices.generate', 'keywords' => ['create', 'receipt'], 'category' => 'financials'],

        ['label' => 'Deployments', 'route' => 'deployments.index', 'group' => 'Operations', 'permission' => 'deployments.view', 'keywords' => ['deploy', 'release']],
        ['label' => 'Monitoring', 'route' => 'monitoring.index', 'group' => 'Operations', 'permission' => 'monitoring.view', 'keywords' => ['monitor', 'metrics']],
        ['label' => 'Redis & Queues', 'route' => 'monitoring.queues', 'group' => 'Operations', 'permission' => 'monitoring.view', 'keywords' => ['redis', 'queue', 'horizon']],
        ['label' => 'Risk Center', 'route' => 'risk-center.index', 'group' => 'Operations', 'permission' => 'risk_center.view', 'keywords' => ['risk', 'alert']],
        ['label' => 'Activity Logs', 'route' => 'activity-logs.index', 'group' => 'Operations', 'permission' => 'activity_logs.view', 'keywords' => ['activity', 'audit', 'log']],
        ['label' => 'Support Tickets', 'route' => 'support-tickets.index', 'group' => 'Operations', 'permission' => 'support.tickets.view', 'keywords' => ['support', 'ticket']],

        ['label' => 'HR & Team', 'route' => 'hr.index', 'group' => 'HR', 'permission' => 'hr.staff.view', 'keywords' => ['hr', 'staff', 'team']],

        ['label' => 'Access Control', 'route' => 'access-control.permissions.index', 'group' => 'Settings', 'permission' => 'rbac.manage', 'keywords' => ['permission', 'rbac'], 'category' => 'settings'],
        ['label' => 'Users & Roles', 'route' => 'users-roles.index', 'group' => 'Settings', 'keywords' => ['user', 'role'], 'category' => 'settings'],
        ['label' => 'System Settings', 'route' => 'system-settings.edit', 'group' => 'Settings', 'permission' => 'system_settings.update', 'keywords' => ['settings', 'billing', 'platform'], 'category' => 'settings'],
        ['label' => 'API & Integrations', 'route' => 'api-credentials.index', 'group' => 'Settings', 'permission' => 'api_credentials.view', 'keywords' => ['api', 'webhook', 'token'], 'category' => 'settings'],
        ['label' => 'Payments Gateway', 'route' => 'settings.payments-gateway.overview', 'group' => 'Settings', 'permission' => 'payments_gateway.view', 'keywords' => ['gateway', 'paybill'], 'category' => 'settings'],
        ['label' => 'Profile', 'route' => 'profile.edit', 'group' => 'Account', 'keywords' => ['profile', 'account'], 'category' => 'settings'],
    ],

];
