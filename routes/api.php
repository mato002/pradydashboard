<?php

use App\Http\Controllers\Api\EnterpriseLicenseCheckController;
use App\Http\Controllers\Api\LicenseCheckController;
use App\Http\Controllers\Api\TenantUsageHeartbeatController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/license/check', LicenseCheckController::class)
    ->middleware(['project.api', 'throttle:license-check']);

Route::post('/license/check', EnterpriseLicenseCheckController::class)
    ->middleware('project.api');

Route::post('/v1/tenant/usage', TenantUsageHeartbeatController::class)
    ->middleware('project.api');
