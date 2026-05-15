<?php

return [

    'sync' => [
        'enabled' => env('INFRA_SYNC_ENABLED', true),
        'interval_minutes' => (int) env('INFRA_SYNC_INTERVAL', 5),
    ],

    'reachability' => [
        'ports' => [443, 80, 22],
        'timeout' => 3,
    ],

    'ssl' => [
        'timeout' => 5,
    ],

    'whm' => [
        'port' => (int) env('INFRA_WHM_PORT', 2087),
        'username' => env('INFRA_WHM_USERNAME', 'root'),
        'api_token' => env('INFRA_WHM_API_TOKEN'),
        'timeout' => 15,
    ],

    'digitalocean' => [
        'token' => env('INFRA_DIGITALOCEAN_TOKEN'),
    ],

    'hetzner' => [
        'token' => env('INFRA_HETZNER_TOKEN'),
    ],

];
