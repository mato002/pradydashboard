<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify Payments Gateway webhook calls to this product installation.
 * Set PAYMENTS_GATEWAY_WEBHOOK_SECRET in .env (from gateway webhook endpoint config).
 */
class AuthenticatePaymentsGatewayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.prady.payments_gateway_webhook_secret');

        if (! filled($secret)) {
            return response()->json(['message' => 'Webhook secret not configured.'], 503);
        }

        $signature = $request->header('X-Payments-Gateway-Signature')
            ?? $request->header('X-Webhook-Signature');

        if (is_string($signature) && $this->validSignature($request, $secret, $signature)) {
            return $next($request);
        }

        $token = $request->bearerToken() ?? $request->header('X-Webhook-Token');

        if (is_string($token) && hash_equals($secret, $token)) {
            return $next($request);
        }

        return response()->json(['message' => 'Invalid webhook credentials.'], 401);
    }

    private function validSignature(Request $request, string $secret, string $signature): bool
    {
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        foreach (['', 'sha256='] as $prefix) {
            if (hash_equals($prefix.$expected, $signature)) {
                return true;
            }
        }

        return hash_equals($expected, $signature);
    }
}
