<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\StaffAssignment;
use App\Models\StaffProfile;
use App\Support\StaffFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffAssignmentController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, StaffProfile $staff): RedirectResponse
    {
        $data = $this->validated($request);
        $this->assertAssignableExists($data['assignable_type'], (int) $data['assignable_id']);

        $assignment = $staff->assignments()->create($data);

        $this->activityLogger->log(
            'staff.assignment_created',
            ActivityLogCategory::HR,
            __('Assignment created for :name', ['name' => $staff->full_name]),
            $assignment,
            null,
            $assignment->only(['assignable_type', 'assignable_id', 'status', 'role_on_assignment']),
        );

        return $this->redirectBack($staff)->with('status', __('Assignment added.'));
    }

    public function update(Request $request, StaffProfile $staff, StaffAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->staff_profile_id === $staff->id, 404);

        $data = $this->validated($request);
        $this->assertAssignableExists($data['assignable_type'], (int) $data['assignable_id']);

        $old = $assignment->only(array_keys($data));
        $assignment->update($data);

        $this->activityLogger->log(
            'staff.assignment_updated',
            ActivityLogCategory::HR,
            __('Assignment updated for :name', ['name' => $staff->full_name]),
            $assignment,
            $old,
            $assignment->only(array_keys($data)),
        );

        return $this->redirectBack($staff)->with('status', __('Assignment updated.'));
    }

    public function destroy(StaffProfile $staff, StaffAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->staff_profile_id === $staff->id, 404);
        $snapshot = $assignment->only(['assignable_type', 'assignable_id', 'status']);
        $assignment->delete();

        $this->activityLogger->log(
            'staff.assignment_deleted',
            ActivityLogCategory::HR,
            __('Assignment removed for :name', ['name' => $staff->full_name]),
            $staff,
            $snapshot,
            null,
        );

        return $this->redirectBack($staff)->with('status', __('Assignment removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'assignable_type' => ['required', Rule::in(array_keys(StaffFormOptions::assignableTypes()))],
            'assignable_id' => ['required', 'integer', 'min:1'],
            'role_on_assignment' => ['nullable', 'string', 'max:255'],
            'responsibility_notes' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(StaffFormOptions::assignmentStatuses()))],
        ]);
    }

    private function assertAssignableExists(string $type, int $id): void
    {
        $exists = match ($type) {
            default => $type::query()->whereKey($id)->exists(),
        };

        if (! $exists) {
            throw ValidationException::withMessages([
                'assignable_id' => __('The selected assignment target does not exist.'),
            ]);
        }
    }

    private function redirectBack(StaffProfile $staff): RedirectResponse
    {
        return redirect()->route('hr.staff.show', ['staff' => $staff, 'tab' => 'assignments']);
    }
}
