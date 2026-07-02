<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePaymentsGatewayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('payment_gateway.webhook_secret');

        if (! filled($secret)) {
            return response()->json([
                'message' => 'Payments Gateway webhook secret is not configured.',
            ], 503);
        }

        $token = $request->bearerToken()
            ?? $request->header('X-Payments-Gateway-Token')
            ?? $request->header('X-Webhook-Token');

        if (! is_string($token) || ! hash_equals($secret, $token)) {
            $signature = $request->header('X-Payments-Gateway-Signature');
            if (is_string($signature) && $this->validSignature($request, $secret, $signature)) {
                return $next($request);
            }

            return response()->json(['message' => 'Invalid webhook credentials.'], 401);
        }

        return $next($request);
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
