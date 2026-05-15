<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Copy into each hosted product system (property, mfi, crm, etc.).
 *
 * Required .env:
 * PRADY_DASHBOARD_URL=https://dashboard.pradytecai.com
 * PRADY_TENANT_KEY=abc-properties
 * PRADY_PRODUCT_KEY=property
 * PRADY_LICENSE_SECRET=...
 * PRADY_PROJECT_API_TOKEN=... (Bearer token from Prady Dashboard → Hosted Project)
 */
class CheckPradyLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $cacheKey = 'prady_license:'.config('services.prady.tenant_key');

        $license = Cache::remember($cacheKey, config('services.prady.cache_ttl', 600), function () {
            return $this->fetchLicense();
        });

        if (! $license) {
            if ($cached = Cache::get($cacheKey.'_last_good')) {
                $license = $cached;
            } else {
                return response()->view('errors.license-unavailable', [], 503);
            }
        }

        Cache::put($cacheKey.'_last_good', $license, config('services.prady.cache_ttl', 600) * 2);

        $request->attributes->set('prady_license', $license);

        if (! ($license['allowed'] ?? false)) {
            return response()->view('errors.tenant-suspended', ['message' => $license['message'] ?? null], 403);
        }

        if (($license['access_level'] ?? '') === 'read_only' && $this->isMutatingRequest($request)) {
            return response()->json([
                'message' => $license['message'] ?? 'Account is in read-only mode.',
            ], 403);
        }

        if (($license['access_level'] ?? '') === 'warning') {
            session()->flash('prady_license_warning', $license['message'] ?? null);
        }

        return $next($request);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchLicense(): ?array
    {
        $url = rtrim((string) config('services.prady.dashboard_url'), '/').'/api/v1/license/check';

        $body = [
            'tenant_key' => config('services.prady.tenant_key'),
            'product_key' => config('services.prady.product_key'),
            'domain' => request()->getHost(),
        ];

        $json = json_encode($body);
        $signature = hash_hmac('sha256', $json, (string) config('services.prady.license_secret'));

        try {
            $response = Http::timeout(8)
                ->withToken((string) config('services.prady.project_api_token'))
                ->withHeaders(['X-Prady-Signature' => $signature])
                ->acceptJson()
                ->post($url, $body);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function isMutatingRequest(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }
}
