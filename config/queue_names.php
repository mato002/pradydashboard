<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Segregated queue names (Redis)
    |--------------------------------------------------------------------------
    */

    'critical' => env('QUEUE_CRITICAL', 'critical'),
    'payments' => env('QUEUE_PAYMENTS', 'payments'),
    'webhooks' => env('QUEUE_WEBHOOKS', 'webhooks'),
    'billing' => env('QUEUE_BILLING', 'billing'),
    'telemetry' => env('QUEUE_TELEMETRY', 'telemetry'),
    'emails' => env('QUEUE_EMAILS', 'emails'),
    'pdf' => env('QUEUE_PDF', 'pdf'),
    'integrations' => env('QUEUE_INTEGRATIONS', 'integrations'),
    'default' => env('QUEUE_DEFAULT', 'default'),
    'low' => env('QUEUE_LOW', 'low'),

    'all' => [
        'critical',
        'payments',
        'webhooks',
        'billing',
        'telemetry',
        'emails',
        'pdf',
        'integrations',
        'default',
        'low',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring labels (display only — does not affect queue routing)
    |--------------------------------------------------------------------------
    */

    'meta' => [
        'critical' => [
            'label' => 'Critical platform jobs',
            'priority' => 'critical',
            'warn_above' => 5,
        ],
        'payments' => [
            'label' => 'Payment capture & reconciliation',
            'priority' => 'critical',
            'warn_above' => 10,
        ],
        'webhooks' => [
            'label' => 'Outbound webhook delivery',
            'priority' => 'high',
            'warn_above' => 25,
        ],
        'billing' => [
            'label' => 'Invoicing & recurring billing',
            'priority' => 'high',
            'warn_above' => 15,
        ],
        'telemetry' => [
            'label' => 'Server telemetry sync',
            'priority' => 'normal',
            'warn_above' => 50,
        ],
        'emails' => [
            'label' => 'Transactional email delivery',
            'priority' => 'normal',
            'warn_above' => 30,
        ],
        'pdf' => [
            'label' => 'Document PDF generation',
            'priority' => 'normal',
            'warn_above' => 20,
        ],
        'integrations' => [
            'label' => 'Third-party integrations',
            'priority' => 'normal',
            'warn_above' => 25,
        ],
        'default' => [
            'label' => 'General background work',
            'priority' => 'normal',
            'warn_above' => 50,
        ],
        'low' => [
            'label' => 'Deferred / low-priority tasks',
            'priority' => 'low',
            'warn_above' => 100,
        ],
    ],

    'worker_command' => [
        'queue_order' => 'critical,payments,webhooks,billing,telemetry,emails,pdf,integrations,default,low',
        'local_windows' => 'php artisan queue:work redis --queue=critical,payments,webhooks,billing,telemetry,emails,pdf,integrations,default,low --tries=3',
        'production_linux' => 'php artisan horizon',
    ],

];
