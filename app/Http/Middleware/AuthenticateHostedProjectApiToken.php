<?php

namespace App\Http\Middleware;

use App\Models\HostedProject;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateHostedProjectApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-Project-Token');

        if (! is_string($token) || $token === '') {
            return response()->json(['message' => 'Hosted project API token is required.'], 401);
        }

        $hostedProject = HostedProject::query()
            ->with('product')
            ->where('api_token', $token)
            ->first();

        if (! $hostedProject) {
            return response()->json(['message' => 'Invalid hosted project API token.'], 401);
        }

        $request->attributes->set('licensed_hosted_project', $hostedProject);
        $request->attributes->set('licensed_project', $hostedProject);

        return $next($request);
    }
}
