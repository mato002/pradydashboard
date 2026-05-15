<?php

// Add to config/services.php in each hosted product system:

'prady' => [
    'dashboard_url' => env('PRADY_DASHBOARD_URL', 'https://dashboard.pradytecai.com'),
    'tenant_key' => env('PRADY_TENANT_KEY'),
    'product_key' => env('PRADY_PRODUCT_KEY'),
    'license_secret' => env('PRADY_LICENSE_SECRET'),
    'project_api_token' => env('PRADY_PROJECT_API_TOKEN'),
    'cache_ttl' => (int) env('PRADY_LICENSE_CACHE_TTL', 600),
],
