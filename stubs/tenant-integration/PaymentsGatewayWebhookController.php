<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives payment events from payments.pradytecai.com.
 * Register: POST /webhooks/payments-gateway/events
 *
 * Customize handle* methods for your billing domain (allocate to invoices, etc.).
 */
class PaymentsGatewayWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = (string) ($payload['event'] ?? $payload['event_type'] ?? 'unknown');

        Log::info('Payments Gateway webhook received', [
            'event' => $event,
            'transaction_uuid' => data_get($payload, 'transaction.uuid'),
        ]);

        if (str_contains($event, 'test') || $event === 'gateway.webhook.test') {
            return response()->json(['received' => true, 'event' => $event]);
        }

        // TODO: dispatch job to process transaction payload for this tenant
        // Example: ProcessPaymentsGatewayEventJob::dispatch($payload);

        return response()->json(['received' => true, 'event' => $event]);
    }
}
