<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Deployments\DeploymentOpsRecorder;
use App\Domain\Deployments\DeploymentOperationsService;
use App\Domain\Deployments\DeploymentPipelineBuilder;
use App\Http\Controllers\Controller;
use App\Support\DemoMode;
use App\Models\Project;
use App\Models\ProjectDeployment;
use Database\Seeders\DeploymentDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    public function __construct(
        private readonly DeploymentOperationsService $operations
    ) {}

    public function index(): View
    {
        if (DemoMode::enabled() && ProjectDeployment::query()->count() < 8) {
            (new DeploymentDemoSeeder)->run();
        }

        $projects = Project::query()->with(['server.latestHealthLog'])->withCount('tenants')->orderBy('name')->get();

        $deploymentHistory = $this->operations->deploymentHistory($projects);
        $kpis = $this->operations->fleetKpis($projects);
        $spark = fn (string $key) => $this->operations->sparkline($key);

        $selected = $deploymentHistory->first();
        $pipeline = $this->operations->pipelineFlow($selected);
        $environments = $this->operations->environments($projects);
        $rollbacks = $this->operations->rollbackCandidates($projects);
        $alerts = $this->operations->alerts($projects);
        $integrations = $this->operations->integrations();
        $metrics = $this->operations->metrics($projects);

        $detailPayload = $deploymentHistory->keyBy('id')->all();
        $projectOptions = $projects->map(fn (Project $p) => ['id' => $p->id, 'name' => $p->name])->values()->all();

        return view('admin.deployments.index', compact(
            'kpis',
            'spark',
            'deploymentHistory',
            'pipeline',
            'environments',
            'rollbacks',
            'alerts',
            'integrations',
            'metrics',
            'detailPayload',
            'projectOptions',
            'projects',
        ));
    }

    public function deploy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'version' => ['nullable', 'string', 'max:100'],
            'environment' => ['required', 'in:production,staging,development,qa,sandbox'],
        ]);

        $project = Project::query()->with('server')->findOrFail($validated['project_id']);
        $version = $validated['version'] ?? ('v'.now()->format('Hi'));

        $notes = DeploymentPipelineBuilder::buildNotes([
            'status' => 'in_progress',
            'environment' => $validated['environment'],
            'branch' => 'main',
            'triggered_by' => auth()->user()?->name ?? 'Manual Ops',
            'version' => $version,
        ], $project);

        $deployment = ProjectDeployment::query()->create([
            'project_id' => $project->id,
            'version' => $version,
            'deployed_at' => now(),
            'notes' => json_encode($notes),
        ]);

        DeploymentOpsRecorder::recordForDeployment($deployment, $notes);

        return redirect()->route('deployments.index')->with('status', __('Deployment queued for :project.', ['project' => $project->name]));
    }

    public function rollback(Request $request, ProjectDeployment $deployment): RedirectResponse
    {
        $deployment->load('project.server');
        $meta = DeploymentPipelineBuilder::forRollback(
            $this->operations->parseNotes($deployment->notes),
            $deployment->project
        );
        $meta['triggered_by'] = auth()->user()?->name ?? 'Rollback';

        $rollback = ProjectDeployment::query()->create([
            'project_id' => $deployment->project_id,
            'version' => $deployment->version.'-rollback',
            'deployed_at' => now(),
            'notes' => json_encode($meta),
        ]);

        DeploymentOpsRecorder::recordForDeployment($rollback, $meta);

        return redirect()->route('deployments.index')->with('status', __('Rollback initiated for :version.', ['version' => $deployment->version]));
    }

    public function redeploy(ProjectDeployment $deployment): RedirectResponse
    {
        $deployment->load('project.server');
        $meta = DeploymentPipelineBuilder::forSuccess(
            $this->operations->parseNotes($deployment->notes),
            $deployment->project
        );
        $meta['triggered_by'] = auth()->user()?->name ?? 'Redeploy';

        $redeploy = ProjectDeployment::query()->create([
            'project_id' => $deployment->project_id,
            'version' => $deployment->version,
            'deployed_at' => now(),
            'notes' => json_encode($meta),
        ]);

        DeploymentOpsRecorder::recordForDeployment($redeploy, $meta);

        return redirect()->route('deployments.index')->with('status', __('Redeploy completed for :version.', ['version' => $deployment->version]));
    }

    public function approve(ProjectDeployment $deployment): RedirectResponse
    {
        $deployment->load('project.server');
        $meta = DeploymentPipelineBuilder::forApproval($this->operations->parseNotes($deployment->notes));

        $deployment->update([
            'deployed_at' => now(),
            'notes' => json_encode($meta),
        ]);

        DeploymentOpsRecorder::recordForDeployment($deployment, $meta);

        return redirect()->route('deployments.index')->with('status', __('Release approved — production deploy started.'));
    }

    public function cancel(ProjectDeployment $deployment): RedirectResponse
    {
        $meta = DeploymentPipelineBuilder::forCancellation($this->operations->parseNotes($deployment->notes));

        $deployment->update(['notes' => json_encode($meta)]);

        return redirect()->route('deployments.index')->with('status', __('Deployment cancelled.'));
    }
}
