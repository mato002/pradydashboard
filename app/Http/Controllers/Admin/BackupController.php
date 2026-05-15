<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\Server;
use Carbon\Carbon;
use Database\Seeders\BackupDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function index(Request $request): View
    {
        if (Backup::query()->doesntExist()) {
            (new BackupDemoSeeder)->run();
        }

        $backups = Backup::query()
            ->with(['server', 'tenant', 'project'])
            ->orderByDesc('started_at')
            ->paginate(12)
            ->withQueryString();

        $schedules = BackupSchedule::query()
            ->with(['server', 'tenant'])
            ->orderBy('name')
            ->get();

        $totalBackups = Backup::query()->count();
        $successfulBackups = Backup::query()->where('status', 'successful')->count();
        $failedBackups = Backup::query()->where('status', 'failed')->count();
        $runningBackups = Backup::query()->where('status', 'running')->count();
        $storageBytes = (int) Backup::query()->whereNotNull('size_bytes')->sum('size_bytes');
        $restorePoints = Backup::query()->where('is_restore_point', true)->where('status', 'successful')->count();

        $lastCompleted = Backup::query()
            ->whereNotNull('duration_seconds')
            ->orderByDesc('completed_at')
            ->first();

        $kpis = [
            'total' => $totalBackups,
            'successful' => $successfulBackups,
            'failed' => $failedBackups,
            'storage' => Backup::formatBytes($storageBytes),
            'storageBytes' => $storageBytes,
            'restorePoints' => $restorePoints,
            'lastRuntime' => $lastCompleted?->formattedDuration() ?? '—',
            'successRate' => $totalBackups > 0 ? round(($successfulBackups / $totalBackups) * 100, 1) : 0,
        ];

        $spark = fn (string $key) => $this->pseudoSparkline($key);

        $storageGrowth = $this->buildStorageGrowthSeries();
        $serverStorage = $this->buildServerStorageBreakdown();
        $tenantStorage = $this->buildTenantStorageBreakdown();
        $alerts = $this->buildAlerts();
        $drMetrics = $this->buildDrMetrics();

        return view('admin.backups.index', compact(
            'backups',
            'schedules',
            'kpis',
            'spark',
            'storageGrowth',
            'serverStorage',
            'tenantStorage',
            'alerts',
            'drMetrics',
            'runningBackups',
        ));
    }

    public function run(Request $request): RedirectResponse
    {
        return redirect()
            ->route('backups.index')
            ->with('status', __('Backup job queued on the next available agent.'));
    }

    public function toggleSchedule(BackupSchedule $schedule): RedirectResponse
    {
        $schedule->update(['enabled' => ! $schedule->enabled]);

        return redirect()
            ->route('backups.index')
            ->with('status', $schedule->enabled
                ? __('Schedule enabled.')
                : __('Schedule paused.'));
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function buildStorageGrowthSeries(): array
    {
        $series = [];
        $base = max(1, (int) Backup::query()->whereNotNull('size_bytes')->sum('size_bytes') / 6);

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $h = crc32('storage-'.$month->format('Y-m'));
            $wave = 0.72 + (($h & 0xFF) / 255) * 0.35;

            $series[] = [
                'label' => $month->format('M'),
                'value' => round($base * $wave * (1 + (5 - $i) * 0.08), 0),
            ];
        }

        return $series;
    }

    /**
     * @return Collection<int, array{name: string, bytes: int, pct: float}>
     */
    private function buildServerStorageBreakdown(): Collection
    {
        $rows = Backup::query()
            ->selectRaw('server_id')
            ->selectRaw('SUM(COALESCE(size_bytes, 0)) as total')
            ->whereNotNull('server_id')
            ->groupBy('server_id')
            ->orderByDesc('total')
            ->with('server')
            ->get();

        if ($rows->isEmpty()) {
            return collect([
                ['name' => __('Unassigned pool'), 'bytes' => (int) Backup::query()->sum('size_bytes'), 'pct' => 100],
            ]);
        }

        $sum = (float) $rows->sum('total');

        return $rows->map(function ($row) use ($sum) {
            $bytes = (int) $row->total;

            return [
                'name' => $row->server?->name ?? __('Unknown server'),
                'bytes' => $bytes,
                'pct' => $sum > 0 ? round(($bytes / $sum) * 100, 1) : 0,
            ];
        })->take(6);
    }

    /**
     * @return Collection<int, array{name: string, bytes: int, pct: float}>
     */
    private function buildTenantStorageBreakdown(): Collection
    {
        $rows = Backup::query()
            ->selectRaw('tenant_id')
            ->selectRaw('SUM(COALESCE(size_bytes, 0)) as total')
            ->whereNotNull('tenant_id')
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->with('tenant')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $sum = (float) $rows->sum('total');

        return $rows->map(function ($row) use ($sum) {
            $bytes = (int) $row->total;

            return [
                'name' => $row->tenant?->company_name ?? __('Unknown tenant'),
                'bytes' => $bytes,
                'pct' => $sum > 0 ? round(($bytes / $sum) * 100, 1) : 0,
            ];
        })->take(5);
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildAlerts(): Collection
    {
        $alerts = collect();

        foreach (Backup::query()->where('status', 'failed')->latest('started_at')->take(3)->get() as $backup) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Failed backup'),
                'body' => __(':name on :target — :note', [
                    'name' => $backup->name,
                    'target' => $backup->server?->name ?? __('unassigned'),
                    'note' => $backup->notes ?? __('Job terminated unexpectedly'),
                ]),
                'time' => $backup->started_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        foreach (Backup::query()->where('status', 'warning')->latest('started_at')->take(2)->get() as $backup) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Integrity warning'),
                'body' => __(':name completed with verification warnings.', ['name' => $backup->name]),
                'time' => $backup->started_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        $unverified = Backup::query()
            ->where('status', 'successful')
            ->where('integrity_verified', false)
            ->count();

        if ($unverified > 0) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Pending integrity checks'),
                'body' => __(':count archives await verification.', ['count' => $unverified]),
                'time' => __('Ops'),
            ]);
        }

        $storageCap = 500_000_000_000; // 500 GB illustrative cap
        $used = (int) Backup::query()->sum('size_bytes');
        if ($used > $storageCap * 0.82) {
            $alerts->push([
                'type' => 'critical',
                'title' => __('Storage threshold'),
                'body' => __('Object storage at :pct% of provisioned capacity.', ['pct' => (int) round(($used / $storageCap) * 100)]),
                'time' => __('Live'),
            ]);
        }

        foreach (Server::query()->whereIn('backup_status', ['failed', 'error'])->take(2)->get() as $server) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Missing snapshot'),
                'body' => __(':server reports degraded backup status.', ['server' => $server->name]),
                'time' => __('Fleet'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('All backup planes nominal'),
                'body' => __('No critical failures detected in the last window.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(8);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDrMetrics(): array
    {
        $lastVerified = Backup::query()
            ->where('integrity_verified', true)
            ->orderByDesc('completed_at')
            ->first();

        $lastRestoreTest = Carbon::now()->subDays(11);

        return [
            'restore_drill_status' => Backup::query()->where('status', 'failed')->exists() ? 'attention' : 'passed',
            'last_restore_test' => $lastRestoreTest->toFormattedDateString(),
            'last_integrity_check' => $lastVerified?->completed_at?->diffForHumans() ?? __('Pending'),
            'rpo' => '15 min',
            'rto' => '45 min',
            'integrity_pass_rate' => $this->integrityPassRate(),
        ];
    }

    private function integrityPassRate(): float
    {
        $successful = Backup::query()->where('status', 'successful')->count();
        if ($successful === 0) {
            return 100.0;
        }

        $verified = Backup::query()->where('status', 'successful')->where('integrity_verified', true)->count();

        return round(($verified / $successful) * 100, 1);
    }

    /**
     * @return array<int, float>
     */
    private function pseudoSparkline(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
