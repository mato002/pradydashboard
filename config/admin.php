<?php

return [
   'quick_links' => [
        'control_plane' => [
            ['label' => 'Tenants', 'route' => 'tenants.index'],
            ['label' => 'Products', 'route' => 'products.index'],
            ['label' => 'Hosted projects', 'route' => 'hosted-projects.index'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions.index'],
            ['label' => 'Access controls', 'route' => 'access-controls.index'],
            ['label' => 'License logs', 'route' => 'license-logs.index'],
        ],
        'billing' => [
            ['label' => 'Invoices', 'route' => 'invoices.index'],
            ['label' => 'Payments', 'route' => 'payments.index'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions.index'],
            ['label' => 'Tenants', 'route' => 'tenants.index'],
        ],
        'infrastructure' => [
            ['label' => 'Servers', 'route' => 'servers.index'],
            ['label' => 'SSL & domains', 'route' => 'ssl-domains.index'],
            ['label' => 'Backups', 'route' => 'backups.index'],
            ['label' => 'Server health', 'route' => 'server-health.index'],
            ['label' => 'Deployments', 'route' => 'deployments.index'],
        ],
        'operations' => [
            ['label' => 'Monitoring', 'route' => 'monitoring.index'],
            ['label' => 'Activity logs', 'route' => 'activity-logs.index'],
            ['label' => 'Support tickets', 'route' => 'support-tickets.index'],
            ['label' => 'API credentials', 'route' => 'api-credentials.index'],
            ['label' => 'Users & roles', 'route' => 'users-roles.index'],
        ],
    ],
];
