<?php

return [

    'webhook_secret' => env('DEPLOYMENTS_WEBHOOK_SECRET'),

    'deploy_timeout_seconds' => (int) env('DEPLOYMENTS_TIMEOUT', 300),

    'agent_timeout_seconds' => (int) env('DEPLOYMENTS_AGENT_TIMEOUT', 30),

];
