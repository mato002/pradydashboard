<?php

return [

    'base_url' => rtrim((string) env('PAYMENTS_GATEWAY_URL', 'https://payments.pradytecai.com'), '/'),

    'admin_token' => env('PAYMENTS_GATEWAY_ADMIN_TOKEN'),

    'timeout' => (int) env('PAYMENTS_GATEWAY_TIMEOUT', 10),

    'retry_attempts' => (int) env('PAYMENTS_GATEWAY_RETRY_ATTEMPTS', 1),

    'webhook_secret' => env('PAYMENTS_GATEWAY_WEBHOOK_SECRET'),

    'sync_enabled' => (bool) env('PAYMENTS_GATEWAY_SYNC_ENABLED', true),

    'sync_lookback_hours' => (int) env('PAYMENTS_GATEWAY_SYNC_LOOKBACK_HOURS', 24),

    'stk_path' => env('PAYMENTS_GATEWAY_STK_PATH', '/pay/stk'),

    'auto_reconcile_enabled' => (bool) env('PAYMENTS_GATEWAY_AUTO_RECONCILE', true),

];
