<?php

namespace App\Http\Middleware;

use App\Models\DeploymentIntegration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDeploymentWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var DeploymentIntegration|null $integration */
        $integration = $request->route('integration');

        if (! $integration instanceof DeploymentIntegration) {
            return response()->json(['message' => 'Deployment integration not found.'], 404);
        }

        $secret = $integration->settings['webhook_secret']
            ?? config('deployments.webhook_secret');

        if (! filled($secret)) {
            return response()->json(['message' => 'Deployment webhook secret is not configured.'], 503);
        }

        $token = $request->bearerToken()
            ?? $request->header('X-Deployment-Token')
            ?? $request->header('X-Hub-Signature-256');

        if ($token === null) {
            return response()->json(['message' => 'Webhook authentication required.'], 401);
        }

        if (str_starts_with((string) $token, 'sha256=')) {
            $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, (string) $token)) {
                return response()->json(['message' => 'Invalid webhook signature.'], 401);
            }

            return $next($request);
        }

        if (! hash_equals($secret, (string) $token)) {
            return response()->json(['message' => 'Invalid webhook token.'], 401);
        }

        return $next($request);
    }
}
