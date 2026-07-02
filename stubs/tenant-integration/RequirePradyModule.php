<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate routes by Prady license enabled_modules.
 * Register alias: 'prady.module' => RequirePradyModule::class
 * Usage: Route::middleware('prady.module:reports')->...
 */
class RequirePradyModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        /** @var array<string, mixed>|null $license */
        $license = $request->attributes->get('prady_license');

        if (! is_array($license)) {
            return response()->json(['message' => 'License context missing.'], 503);
        }

        $enabled = $license['enabled_modules'] ?? [];

        if (! is_array($enabled) || ! in_array($module, $enabled, true)) {
            return response()->json([
                'message' => __('The :module module is not enabled for this account.', ['module' => $module]),
            ], 403);
        }

        return $next($request);
    }
}
