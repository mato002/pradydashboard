<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsFresh
{
    /** @var list<string> */
    private const BYPASS_ROUTE_NAMES = [
        'password.expired',
        'password.expired.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->mustChangePassword()) {
            return $next($request);
        }

        if ($request->routeIs(self::BYPASS_ROUTE_NAMES)) {
            return $next($request);
        }

        return redirect()->route('password.expired');
    }
}
