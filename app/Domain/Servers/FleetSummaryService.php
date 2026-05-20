<?php

namespace App\Domain\Servers;

use App\Models\Backup;
use App\Models\Server;
use App\Models\ServerHealthLog;
use App\Support\OperationalMetrics;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FleetSummaryService
{
    private const POINTS = 12;

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $servers = Server::query()->get();
        $online = $servers->whereIn('status', ['online', 'warning']);
        $sslProtected = $servers->filter(fn (Server $s) => $this->isSslHealthy($s->ssl_status))->count();
        $withBackup = $servers->filter(fn (Server $s) => $this->hasBackupPolicy($s->backup_status))->count();
        $totalCpu = (int) $servers->sum('cpu_cores');
        $avgDisk = round((float) $servers->avg('disk_usage_percent'), 1);
        $backupCoverage = $servers->isEmpty() ? 0 : (int) round(($withBackup / $servers->count()) * 100);
        $backupCount = Backup::query()->where('status', 'completed')->count();
        $synced = $servers->filter(fn (Server $s) => $s->last_synced_at !== null);
        $uptime = $synced->isEmpty()
            ? null
            : round(($synced->whereIn('status', ['online', 'warning'])->count() / max(1, $synced->count())) * 100, 1);

        $totalTrend = $this->recentRegistrationTrend($servers);

        return [
            'total' => $servers->count(),
            'healthy' => $online->count(),
            'ssl_protected' => $sslProtected,
            'backup_coverage' => $backupCoverage,
            'cpu_capacity' => $totalCpu,
            'avg_disk' => $avgDisk,
            'fleet_uptime' => $uptime,
            'backup_jobs' => $backupCount,
            'total_trend' => $totalTrend,
            'spark' => $this->buildSparklines($servers),
        ];
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return array<string, list<int|float>>
     */
    private function buildSparklines(Collection $servers): array
    {
        $days = $this->dayBuckets();
        $diskFromLogs = $this->diskSeriesFromHealthLogs($days);

        return [
            'total' => $this->seriesServerCount($servers, $days),
            'healthy' => $this->seriesHealthy($servers, $days),
            'ssl' => $this->seriesSslProtected($servers, $days),
            'backup' => $this->seriesBackupCoverage($servers, $days),
            'cpu' => $this->seriesCpuCapacity($servers, $days),
            'disk' => $diskFromLogs !== [] ? $diskFromLogs : $this->seriesAvgDiskFromServers($servers, $days),
            'uptime' => $this->seriesFleetUptime($servers, $days),
        ];
    }

    /**
     * @return list<Carbon>
     */
    private function dayBuckets(): array
    {
        $buckets = [];
        for ($i = self::POINTS - 1; $i >= 0; $i--) {
            $buckets[] = now()->subDays($i)->endOfDay();
        }

        return $buckets;
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<int>
     */
    private function seriesServerCount(Collection $servers, array $days): array
    {
        return array_map(
            fn (Carbon $day) => $servers->filter(fn (Server $s) => $s->created_at <= $day)->count(),
            $days,
        );
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<int>
     */
    private function seriesHealthy(Collection $servers, array $days): array
    {
        return array_map(
            fn (Carbon $day) => $servers
                ->filter(fn (Server $s) => $s->created_at <= $day)
                ->whereIn('status', ['online', 'warning'])
                ->count(),
            $days,
        );
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<int>
     */
    private function seriesSslProtected(Collection $servers, array $days): array
    {
        return array_map(
            fn (Carbon $day) => $servers
                ->filter(fn (Server $s) => $s->created_at <= $day)
                ->filter(fn (Server $s) => $this->isSslHealthy($s->ssl_status))
                ->count(),
            $days,
        );
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<int>
     */
    private function seriesBackupCoverage(Collection $servers, array $days): array
    {
        return array_map(function (Carbon $day) use ($servers) {
            $fleet = $servers->filter(fn (Server $s) => $s->created_at <= $day);
            if ($fleet->isEmpty()) {
                return 0;
            }

            $withBackup = $fleet->filter(fn (Server $s) => $this->hasBackupPolicy($s->backup_status))->count();

            return (int) round(($withBackup / $fleet->count()) * 100);
        }, $days);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<int>
     */
    private function seriesCpuCapacity(Collection $servers, array $days): array
    {
        return array_map(
            fn (Carbon $day) => (int) $servers
                ->filter(fn (Server $s) => $s->created_at <= $day)
                ->sum('cpu_cores'),
            $days,
        );
    }

    /**
     * @param  list<Carbon>  $days
     * @return list<float>
     */
    private function diskSeriesFromHealthLogs(array $days): array
    {
        $logs = ServerHealthLog::query()
            ->where('checked_at', '>=', now()->subDays(self::POINTS)->startOfDay())
            ->whereNotNull('disk_percent')
            ->get(['disk_percent', 'checked_at']);

        if ($logs->isEmpty()) {
            return [];
        }

        $series = [];
        foreach ($days as $day) {
            $dayLogs = $logs->filter(fn (ServerHealthLog $log) => $log->checked_at->lte($day) && $log->checked_at->gte($day->copy()->startOfDay()));
            $series[] = $dayLogs->isEmpty()
                ? 0.0
                : round((float) $dayLogs->avg('disk_percent'), 1);
        }

        return OperationalMetrics::normalizeSparkline($series);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<float>
     */
    private function seriesAvgDiskFromServers(Collection $servers, array $days): array
    {
        $series = array_map(function (Carbon $day) use ($servers) {
            $fleet = $servers->filter(fn (Server $s) => $s->created_at <= $day && $s->disk_usage_percent !== null);
            if ($fleet->isEmpty()) {
                return 0.0;
            }

            return round((float) $fleet->avg('disk_usage_percent'), 1);
        }, $days);

        return OperationalMetrics::normalizeSparkline($series);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @param  list<Carbon>  $days
     * @return list<float>
     */
    private function seriesFleetUptime(Collection $servers, array $days): array
    {
        $series = array_map(function (Carbon $day) use ($servers) {
            $synced = $servers->filter(fn (Server $s) => $s->created_at <= $day && $s->last_synced_at !== null);
            if ($synced->isEmpty()) {
                return 0.0;
            }

            return round(($synced->whereIn('status', ['online', 'warning'])->count() / $synced->count()) * 100, 1);
        }, $days);

        return OperationalMetrics::normalizeSparkline($series);
    }

    /**
     * @param  Collection<int, Server>  $servers
     */
    private function recentRegistrationTrend(Collection $servers): ?string
    {
        $cutoff = now()->subDays(7)->startOfDay();
        $added = $servers->filter(fn (Server $s) => $s->created_at >= $cutoff)->count();

        if ($added === 0) {
            return null;
        }

        return '+'.$added;
    }

    private function isSslHealthy(?string $status): bool
    {
        if (! $status) {
            return false;
        }

        $s = strtolower($status);

        return str_contains($s, 'valid') || str_contains($s, 'active') || str_contains($s, 'ok');
    }

    private function hasBackupPolicy(?string $status): bool
    {
        if (! filled($status)) {
            return false;
        }

        return ! str_contains(strtolower((string) $status), 'fail');
    }
}
