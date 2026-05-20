<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outbound API check timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Used when Prady calls tenant system endpoints or provider health URLs.
    | Keep between 5–10 seconds to avoid blocking admin requests.
    |
    */
    'api_timeout_seconds' => (int) env('INTEGRATIONS_API_TIMEOUT', 8),

    'api_connect_timeout_seconds' => (int) env('INTEGRATIONS_API_CONNECT_TIMEOUT', 5),

];
