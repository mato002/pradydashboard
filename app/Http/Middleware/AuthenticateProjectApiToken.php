<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateProjectApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-Project-Token');

        if (! is_string($token) || $token === '') {
            return response()->json(['message' => 'Project API token is required.'], 401);
        }

        $project = Project::query()->where('api_token', $token)->first();

        if (! $project) {
            return response()->json(['message' => 'Invalid project API token.'], 401);
        }

        $request->attributes->set('licensed_project', $project);

        return $next($request);
    }
}
