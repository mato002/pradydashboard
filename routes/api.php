<?php

use App\Http\Controllers\Api\BackupAgentApiController;
use App\Http\Controllers\Api\DeploymentWebhookController;
use App\Http\Controllers\Api\EnterpriseLicenseCheckController;
use App\Http\Controllers\Api\LicenseCheckController;
use App\Http\Controllers\Api\PaymentsGatewayWebhookController;
use App\Http\Controllers\Api\TenantUsageHeartbeatController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/license/check', LicenseCheckController::class)
    ->middleware(['project.api', 'throttle:license-check']);

Route::post('/license/check', EnterpriseLicenseCheckController::class)
    ->middleware('project.api');

Route::post('/v1/tenant/usage', TenantUsageHeartbeatController::class)
    ->middleware('project.api');

Route::middleware(['project.api', 'throttle:60,1'])->prefix('v1/backups/agents')->group(function () {
    Route::post('/register', [BackupAgentApiController::class, 'registerAgent']);
    Route::post('/heartbeat', [BackupAgentApiController::class, 'heartbeat']);
});

Route::post('/v1/payments-gateway/webhooks', PaymentsGatewayWebhookController::class)
    ->middleware(['payments.gateway.webhook', 'throttle:60,1'])
    ->name('api.payments-gateway.webhooks');

Route::post('/v1/deployments/webhooks/{integration}', DeploymentWebhookController::class)
    ->middleware(['deployment.webhook', 'throttle:120,1'])
    ->name('api.deployments.webhooks');
