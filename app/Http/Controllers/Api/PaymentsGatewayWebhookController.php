<?php

namespace App\Http\Controllers\Api;

use App\Domain\Billing\PaymentsGatewayTransactionImporter;
use App\Http\Controllers\Controller;
use App\Jobs\Billing\ReconcilePaymentsBatchJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentsGatewayWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentsGatewayTransactionImporter $importer): JsonResponse
    {
        $payload = $request->all();
        $event = (string) ($payload['event'] ?? $payload['event_type'] ?? 'transaction.updated');
        $transaction = $payload['transaction'] ?? $payload['data'] ?? $payload;

        if (! is_array($transaction) || ! isset($transaction['uuid'])) {
            return response()->json(['message' => 'Transaction payload with uuid is required.'], 422);
        }

        $payment = $importer->import($transaction);

        if ($payment !== null
            && $payment->status === 'successful'
            && config('payment_gateway.auto_reconcile_enabled', true)) {
            ReconcilePaymentsBatchJob::dispatch();
        }

        return response()->json([
            'received' => true,
            'event' => $event,
            'payment_id' => $payment?->id,
            'transaction_uuid' => $transaction['uuid'],
        ]);
    }
}
