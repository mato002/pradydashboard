<?php

// routes/web.php — payment events from payments.pradytecai.com (no CSRF)

use App\Http\Controllers\Webhooks\PaymentsGatewayWebhookController;
use App\Http\Middleware\AuthenticatePaymentsGatewayWebhook;

Route::post('/webhooks/payments-gateway/events', PaymentsGatewayWebhookController::class)
    ->middleware(AuthenticatePaymentsGatewayWebhook::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('webhooks.payments-gateway.events');

// Canonical URL registered on the gateway:
// https://{tenant_domain}/webhooks/payments-gateway/events
