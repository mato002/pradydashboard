<?php

namespace App\Domain\Hr;

use App\Models\HrDepartment;
use App\Models\StaffAssignment;
use App\Models\StaffDocument;
use App\Models\StaffProfile;
use App\Support\Cache\OperationalCache;
use Illuminate\Support\Collection;

class HrOverview
{
    public function __construct(
        private readonly OperationalCache $operationalCache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        return $this->operationalCache->remember(
            'hr',
            'overview',
            config('redis_cache.ttl.hr_overview', 600),
            fn () => $this->computeMetrics(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function computeMetrics(): array
    {
        $activeStaff = StaffProfile::query()->where('status', 'active')->count();
        $exitedStaff = StaffProfile::query()->where('status', 'exited')->count();

        $byDepartment = HrDepartment::query()
            ->withCount(['staff' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get()
            ->map(fn (HrDepartment $d) => [
                'name' => $d->name,
                'code' => $d->code,
                'count' => $d->staff_count,
            ]);

        $openAssignments = StaffAssignment::query()->where('status', 'active')->count();

        $staffWithAssignments = StaffAssignment::query()
            ->where('status', 'active')
            ->distinct('staff_profile_id')
            ->count('staff_profile_id');

        $staffWithoutAssignments = max(0, $activeStaff - $staffWithAssignments);

        $upcomingExpiries = StaffDocument::query()
            ->where('document_type', 'contract')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->with('staffProfile')
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        return [
            'active_staff' => $activeStaff,
            'exited_staff' => $exitedStaff,
            'by_department' => $byDepartment,
            'open_assignments' => $openAssignments,
            'staff_without_assignments' => $staffWithoutAssignments,
            'upcoming_contract_expiries' => $upcomingExpiries,
            'recent_exits' => StaffProfile::query()
                ->where('status', 'exited')
                ->orderByDesc('end_date')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, StaffAssignment>
     */
    public function assignmentsFor(mixed $model): Collection
    {
        if (! method_exists($model, 'activeStaffAssignments')) {
            return collect();
        }

        return $model->activeStaffAssignments()->get();
    }
}
