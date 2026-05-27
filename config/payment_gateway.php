<?php

return [

    'base_url' => rtrim((string) env('PAYMENTS_GATEWAY_URL', 'https://payments.pradytecai.com'), '/'),

    'admin_token' => env('PAYMENTS_GATEWAY_ADMIN_TOKEN'),

    'timeout' => (int) env('PAYMENTS_GATEWAY_TIMEOUT', 30),

    'retry_attempts' => (int) env('PAYMENTS_GATEWAY_RETRY_ATTEMPTS', 2),

];
