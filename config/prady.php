<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Control plane (master system)
    |--------------------------------------------------------------------------
    |
    | Prady Dashboard is the central tenant, billing, license, and access
    | control system. Hosted product apps (property, mfi, crm, etc.) call
    | back via HTTP API — never shared sessions or direct DB access.
    |
    */

    'license' => [
        'cache_ttl_seconds' => (int) env('PRADY_LICENSE_CACHE_TTL', 600),
        'rate_limit_per_minute' => (int) env('PRADY_LICENSE_RATE_LIMIT', 120),
        'require_signature_when_secret_set' => env('PRADY_LICENSE_REQUIRE_SIGNATURE', true),
        'log_checks' => env('PRADY_LICENSE_LOG_CHECKS', true),
    ],

];
