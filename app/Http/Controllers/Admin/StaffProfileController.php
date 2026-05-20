<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Activity\ActivityLogger;
use App\Domain\Support\SupportOperationsSummary;
use App\Support\ActivityLogCategory;
use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use App\Models\Project;
use App\Models\StaffProfile;
use App\Models\User;
use App\Support\StaffFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(Request $request): View
    {
        $query = StaffProfile::query()
            ->with('department')
            ->orderBy('full_name');

        if ($dept = $request->query('department')) {
            $query->where('hr_department_id', $dept);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('staff_number', 'like', "%{$search}%");
            });
        }

        return view('admin.hr.staff.index', [
            'staff' => $query->paginate(15)->withQueryString(),
            'departments' => HrDepartment::query()->orderBy('name')->get(),
            'statuses' => StaffFormOptions::staffStatuses(),
            'filters' => [
                'q' => $request->query('q'),
                'department' => $request->query('department'),
                'status' => $request->query('status'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.hr.staff.create', $this->formData(new StaffProfile));
    }

    public function store(Request $request): RedirectResponse
    {
        $staff = StaffProfile::query()->create($this->validated($request));

        $this->activityLogger->log(
            'staff.created',
            ActivityLogCategory::HR,
            __('Staff profile created: :name', ['name' => $staff->full_name]),
            $staff,
            null,
            $staff->only(['full_name', 'email', 'status', 'employment_type']),
        );

        return redirect()
            ->route('hr.staff.show', $staff)
            ->with('status', __('Staff profile created.'));
    }

    public function show(Request $request, StaffProfile $staff): View
    {
        $tab = in_array($request->query('tab'), ['overview', 'assignments', 'documents', 'payroll'], true)
            ? $request->query('tab')
            : 'overview';

        $staff->load([
            'department',
            'user',
            'assignments.assignable',
            'documents.uploader',
        ]);

        $supportSummary = app(SupportOperationsSummary::class);

        return view('admin.hr.staff.show', array_merge($this->formData($staff), [
            'staff' => $staff,
            'tab' => $tab,
            'assignedTickets' => $supportSummary->forStaff($staff->id),
            'staffFollowUps' => $supportSummary->followUpsForStaff($staff->id),
            'activityLogs' => app(ActivityLogQuery::class)->forContext(staffProfileId: $staff->id),
            'operationalRisks' => app(\App\Domain\Operations\OperationalRiskScanner::class)->forStaff($staff->id),
            'assignableTypes' => StaffFormOptions::assignableTypes(),
            'assignableOptionsMap' => collect(StaffFormOptions::assignableTypes())
                ->mapWithKeys(fn ($label, $class) => [$class => StaffFormOptions::assignableOptions($class)]),
            'assignmentStatuses' => StaffFormOptions::assignmentStatuses(),
            'documentTypes' => \App\Support\StaffDocumentOptions::documentTypes(),
        ]));
    }

    public function edit(StaffProfile $staff): View
    {
        return view('admin.hr.staff.edit', $this->formData($staff));
    }

    public function update(Request $request, StaffProfile $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff);
        $old = $staff->only(array_keys($data));
        $staff->update($data);

        $this->activityLogger->log(
            'staff.updated',
            ActivityLogCategory::HR,
            __('Staff profile updated: :name', ['name' => $staff->full_name]),
            $staff,
            $old,
            $staff->only(array_keys($data)),
        );

        return redirect()
            ->route('hr.staff.show', $staff)
            ->with('status', __('Staff profile updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(StaffProfile $staff): array
    {
        return [
            'staff' => $staff,
            'departments' => HrDepartment::query()->where('status', 'active')->orderBy('name')->get(),
            'employmentTypes' => StaffFormOptions::employmentTypes(),
            'statuses' => StaffFormOptions::staffStatuses(),
            'users' => User::query()->orderBy('name')->pluck('name', 'id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?StaffProfile $staff = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'hr_department_id' => ['nullable', 'exists:hr_departments,id'],
            'staff_number' => ['nullable', 'string', 'max:40', Rule::unique('staff_profiles', 'staff_number')->ignore($staff?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(array_keys(StaffFormOptions::employmentTypes()))],
            'status' => ['required', Rule::in(array_keys(StaffFormOptions::staffStatuses()))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'emergency_contact' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
