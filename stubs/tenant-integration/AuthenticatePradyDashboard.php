<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require Bearer token or X-API-Key matching PRADY_DASHBOARD_API_TOKEN in .env.
 */
class AuthenticatePradyDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.prady.dashboard_api_token');

        if (! filled($expected)) {
            return response()->json(['message' => 'Dashboard API token not configured.'], 503);
        }

        $provided = $this->extractToken($request);

        if (! filled($provided) || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();
        if (filled($bearer)) {
            return $bearer;
        }

        $header = $request->header('X-API-Key');

        return filled($header) ? (string) $header : null;
    }
}
