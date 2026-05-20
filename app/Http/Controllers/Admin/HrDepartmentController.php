<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use App\Models\StaffProfile;
use App\Support\StaffFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrDepartmentController extends Controller
{
    public function index(): View
    {
        $departments = HrDepartment::query()
            ->with('manager')
            ->withCount(['staff' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return view('admin.hr.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.hr.departments.create', [
            'department' => new HrDepartment,
            'managers' => $this->managerOptions(),
            'statuses' => StaffFormOptions::departmentStatuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        HrDepartment::query()->create($data);

        return redirect()
            ->route('hr.departments.index')
            ->with('status', __('Department created.'));
    }

    public function edit(HrDepartment $department): View
    {
        return view('admin.hr.departments.edit', [
            'department' => $department,
            'managers' => $this->managerOptions(),
            'statuses' => StaffFormOptions::departmentStatuses(),
        ]);
    }

    public function update(Request $request, HrDepartment $department): RedirectResponse
    {
        $department->update($this->validated($request, $department));

        return redirect()
            ->route('hr.departments.index')
            ->with('status', __('Department updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?HrDepartment $department = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('hr_departments', 'code')->ignore($department?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'manager_staff_id' => ['nullable', 'exists:staff_profiles,id'],
            'status' => ['required', Rule::in(array_keys(StaffFormOptions::departmentStatuses()))],
        ]);

        $data['code'] = Str::slug($data['code'], '_');

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function managerOptions(): array
    {
        return StaffProfile::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->pluck('full_name', 'id')
            ->all();
    }
}
