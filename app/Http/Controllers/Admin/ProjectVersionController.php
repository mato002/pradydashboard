<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ActivityLogCategory;
use App\Models\ProjectVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectVersionController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'release_date' => ['nullable', 'date'],
            'release_type' => ['required', 'in:major,minor,patch,hotfix,security'],
            'minimum_supported_version' => ['nullable', 'string', 'max:50'],
            'changelog' => ['nullable', 'string'],
            'is_current' => ['boolean'],
        ]);

        if ($request->boolean('is_current')) {
            $project->versions()->update(['is_current' => false]);
            $project->update([
                'version' => $data['version'],
                'min_supported_version' => $data['minimum_supported_version'] ?? $project->min_supported_version,
                'latest_release_date' => $data['release_date'] ?? now(),
            ]);
        }

        $version = $project->versions()->create([
            ...$data,
            'is_current' => $request->boolean('is_current'),
        ]);

        $this->activityLogger->log(
            'project.version_created',
            ActivityLogCategory::PROJECT,
            __('Version :version registered for :project', ['version' => $version->version, 'project' => $project->name]),
            $version,
            null,
            $version->only(['version', 'release_type', 'is_current']),
        );

        return back()->with('status', __('Version registered.'));
    }

    public function destroy(Project $project, ProjectVersion $version): RedirectResponse
    {
        abort_unless($version->project_id === $project->id, 404);

        $label = $version->version;
        $version->delete();

        $this->activityLogger->log(
            'project.version_deleted',
            ActivityLogCategory::PROJECT,
            __('Version :version removed from :project', ['version' => $label, 'project' => $project->name]),
            $project,
            ['version' => $label],
            null,
        );

        return back()->with('status', __('Version removed.'));
    }
}
