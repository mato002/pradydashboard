<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ActivityLogCategory;
use App\Models\ProjectModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectModuleController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'is_billable' => ['boolean'],
            'default_enabled' => ['boolean'],
            'monthly_price' => ['nullable', 'numeric', 'min:0'],
            'setup_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $module = $project->modules()->create([
            ...$data,
            'code' => strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace('-', '_', $data['code']))),
            'is_billable' => $request->boolean('is_billable'),
            'default_enabled' => $request->boolean('default_enabled'),
        ]);

        $this->activityLogger->log(
            'project.module_created',
            ActivityLogCategory::PROJECT,
            __('Module created: :name on :project', ['name' => $module->name, 'project' => $project->name]),
            $module,
            null,
            $module->only(['name', 'code', 'status', 'is_billable']),
        );

        return back()->with('status', __('Module added.'));
    }

    public function destroy(Project $project, ProjectModule $module): RedirectResponse
    {
        abort_unless($module->project_id === $project->id, 404);

        $name = $module->name;
        $module->delete();

        $this->activityLogger->log(
            'project.module_deleted',
            ActivityLogCategory::PROJECT,
            __('Module removed: :name from :project', ['name' => $name, 'project' => $project->name]),
            $project,
            ['module' => $name],
            null,
        );

        return back()->with('status', __('Module removed.'));
    }
}
