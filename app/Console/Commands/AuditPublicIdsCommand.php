<?php

namespace App\Console\Commands;

use App\Support\PublicId\PublicIdAuditor;
use App\Support\PublicId\PublicIdRouteCoverage;
use Illuminate\Console\Command;

class AuditPublicIdsCommand extends Command
{
    protected $signature = 'public-ids:audit {--repair : Backfill missing public_ids only (never overwrite existing)}';

    protected $description = 'Audit public_id health for route-exposed resources';

    public function handle(PublicIdAuditor $auditor, PublicIdRouteCoverage $coverage): int
    {
        if ($this->option('repair')) {
            return $this->repair($auditor);
        }

        $this->info(__('Public ID audit'));
        $this->newLine();

        $rows = $auditor->audit();
        $this->table(
            ['Resource', 'Count', 'Missing', 'Duplicate', 'Invalid Format', 'Status'],
            collect($rows)->map(fn (array $row) => [
                $row['resource'],
                $row['count'],
                $row['missing'],
                $row['duplicate'],
                $row['invalid_format'],
                $row['status'],
            ])->all(),
        );

        $this->newLine();
        $this->comment(__('Route model binding coverage (internal):'));
        $this->table(
            ['Route', 'Parameter', 'Protected'],
            collect($coverage->routes())->map(fn (array $row) => [
                $row['route'],
                $row['parameter'],
                $row['protected'] ? 'yes' : 'no',
            ])->all(),
        );

        if ($auditor->hasIssues()) {
            $this->newLine();
            $this->error(__('Public ID audit found issues. Run with --repair to backfill missing values only.'));

            return self::FAILURE;
        }

        $this->newLine();
        $this->info(__('All public ID resources are healthy.'));

        return self::SUCCESS;
    }

    private function repair(PublicIdAuditor $auditor): int
    {
        $before = $auditor->audit();
        $hadMissing = collect($before)->sum('missing') > 0;

        if (! $hadMissing) {
            $this->info(__('No missing public_ids to repair.'));

            return $auditor->hasIssues() ? self::FAILURE : self::SUCCESS;
        }

        $result = $auditor->repairMissing();
        $this->info(__('Repaired :count missing public_id(s). Skipped :skipped existing record(s).', [
            'count' => $result['repaired'],
            'skipped' => $result['skipped'],
        ]));

        if ($auditor->hasIssues()) {
            $this->warn(__('Remaining issues (duplicates, invalid format) require manual review — not auto-repaired.'));

            return self::FAILURE;
        }

        $this->info(__('Public ID repair complete. All resources healthy.'));

        return self::SUCCESS;
    }
}
