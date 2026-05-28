<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Operational cache (Redis-backed summaries & locks)
    |--------------------------------------------------------------------------
    |
    | Short-lived aggregates for dashboards and KPI cards. Live financial
    | truth (invoice balances, payment allocation, license enforcement) is
    | never cached here — only read-heavy summary endpoints.
    |
    */

    'enabled' => env('REDIS_CACHE_ENABLED', true),

    'schema_version' => env('REDIS_CACHE_SCHEMA', 'v1'),

    'ttl' => [
        'dashboard' => (int) env('REDIS_CACHE_TTL_DASHBOARD', 120),
        'billing_summary' => (int) env('REDIS_CACHE_TTL_BILLING', 120),
        'financial_overview' => (int) env('REDIS_CACHE_TTL_FINANCIAL', 180),
        'tenant_command_center' => (int) env('REDIS_CACHE_TTL_TENANT_CC', 120),
        'support_summary' => (int) env('REDIS_CACHE_TTL_SUPPORT', 120),
        'fleet_summary' => (int) env('REDIS_CACHE_TTL_FLEET', 90),
        'risk_attention' => (int) env('REDIS_CACHE_TTL_RISK', 120),
        'hr_overview' => (int) env('REDIS_CACHE_TTL_HR', 600),
        'settings' => (int) env('REDIS_CACHE_TTL_SETTINGS', 3600),
    ],

    'locks' => [
        'invoice_number' => (int) env('REDIS_LOCK_INVOICE_NUMBER', 15),
        'recurring_schedule' => (int) env('REDIS_LOCK_RECURRING', 120),
        'server_sync' => (int) env('REDIS_LOCK_SERVER_SYNC', 120),
        'payment_reconcile' => (int) env('REDIS_LOCK_PAYMENT', 30),
        'payment_reference' => (int) env('REDIS_LOCK_PAYMENT_REF', 30),
        'billing_recurring_run' => (int) env('REDIS_LOCK_BILLING_RUN', 300),
    ],

];
