<?php

// routes/api.php — add inside your API routes group:

use App\Http\Controllers\Api\SystemInfoController;
use App\Http\Middleware\AuthenticatePradyDashboard;

Route::middleware(AuthenticatePradyDashboard::class)
    ->get('/system/info', SystemInfoController::class);

// Full URL example: https://tenant-domain.com/api/system/info
