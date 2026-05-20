<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/**
 * Copy into each Prady product installation.
 * Register route: GET /api/system/info (see routes-api-snippet.php).
 * Protect with AuthenticatePradyDashboard middleware.
 */
class SystemInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'project' => config('services.prady.product_name', config('app.name')),
            'tenant_code' => config('services.prady.tenant_code'),
            'version' => config('app.version', '1.0.0'),
            'build' => (string) config('services.prady.build', ''),
            'commit' => (string) config('services.prady.commit', ''),
            'environment' => app()->environment(),
            'app_url' => config('app.url'),
            'last_deployed_at' => config('services.prady.last_deployed_at'),
            'usage' => $this->usageMetrics(),
            'health' => $this->healthChecks(),
        ]);
    }

    /**
     * @return array<string, int|float>
     */
    private function usageMetrics(): array
    {
        return array_filter([
            'users' => $this->safeCount('users'),
            'branches' => $this->safeCount('branches'),
            'storage_mb' => $this->storageMegabytes(),
            'sms_sent_month' => (int) config('services.prady.sms_sent_month', 0),
            'api_requests_today' => (int) config('services.prady.api_requests_today', 0),
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, string>
     */
    private function healthChecks(): array
    {
        return [
            'database' => $this->probeDatabase(),
            'queue' => $this->probeQueue(),
            'scheduler' => $this->probeScheduler(),
            'storage' => $this->probeStorage(),
        ];
    }

    private function probeDatabase(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function probeQueue(): string
    {
        try {
            $connection = config('queue.default');
            Queue::connection($connection)->size();

            return 'ok';
        } catch (\Throwable) {
            return 'degraded';
        }
    }

    private function probeScheduler(): string
    {
        return filled(config('services.prady.scheduler_last_run'))
            ? 'ok'
            : 'unknown';
    }

    private function probeStorage(): string
    {
        try {
            Storage::disk(config('filesystems.default'))->exists('.');

            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function safeCount(string $table): ?int
    {
        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable) {
            return null;
        }
    }

    private function storageMegabytes(): ?int
    {
        try {
            $bytes = 0;
            foreach (Storage::allFiles() as $file) {
                $bytes += Storage::size($file);
            }

            return (int) round($bytes / 1024 / 1024);
        } catch (\Throwable) {
            return null;
        }
    }
}
