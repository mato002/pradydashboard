<?php

// Add to config/services.php in each hosted product system:

'prady' => [
    'dashboard_url' => env('PRADY_DASHBOARD_URL', 'https://dashboard.pradytecai.com'),
    'dashboard_api_token' => env('PRADY_DASHBOARD_API_TOKEN'),
    'tenant_key' => env('PRADY_TENANT_KEY'),
    'tenant_code' => env('PRADY_TENANT_CODE'),
    'product_name' => env('PRADY_PRODUCT_NAME', config('app.name')),
    'product_key' => env('PRADY_PRODUCT_KEY'),
    'license_secret' => env('PRADY_LICENSE_SECRET'),
    'project_api_token' => env('PRADY_PROJECT_API_TOKEN'),
    'build' => env('PRADY_BUILD'),
    'commit' => env('PRADY_COMMIT'),
    'last_deployed_at' => env('PRADY_LAST_DEPLOYED_AT'),
    'cache_ttl' => (int) env('PRADY_LICENSE_CACHE_TTL', 600),
],
