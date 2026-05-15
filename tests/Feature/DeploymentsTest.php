<?php

namespace Tests\Feature;

use App\Domain\Deployments\DeploymentPipelineBuilder;
use App\Models\DeploymentIntegration;
use App\Models\DeploymentOpsEvent;
use App\Models\Project;
use App\Models\ProjectDeployment;
use App\Models\User;
use Database\Seeders\DeploymentDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_deployments_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('deployments.index'));

        $response->assertOk();
        $response->assertSee('Release Management Center');
        $response->assertSee('Deployment history');
        $response->assertSee('New Deployment');
    }

    public function test_deploy_creates_database_record_with_pipeline_metadata(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Deploy Target',
            'domain' => 'deploy-target.test',
            'status' => 'active',
            'version' => 'v1.0.0',
        ]);

        $response = $this->actingAs($user)->post(route('deployments.deploy'), [
            'project_id' => $project->id,
            'version' => 'v9.9.9',
            'environment' => 'staging',
        ]);

        $response->assertRedirect(route('deployments.index'));

        $deployment = ProjectDeployment::query()->first();
        $this->assertNotNull($deployment);
        $this->assertSame('v9.9.9', $deployment->version);

        $meta = json_decode($deployment->notes, true);
        $this->assertIsArray($meta['pipeline_stages'] ?? null);
        $this->assertNotEmpty($meta['build_logs'] ?? []);
        $this->assertSame('staging', $meta['environment']);

        $this->assertGreaterThan(0, DeploymentOpsEvent::query()->where('type', 'container_deploy')->count());
    }

    public function test_integrations_load_from_database(): void
    {
        $user = User::factory()->create();

        DeploymentIntegration::query()->create([
            'provider' => 'github',
            'name' => 'GitHub Enterprise',
            'status' => 'connected',
            'repositories_count' => 5,
            'webhooks_count' => 2,
            'last_synced_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('deployments.index'));

        $response->assertOk();
        $response->assertSee('GitHub Enterprise');
    }

    public function test_pipeline_builder_stores_stages_in_notes(): void
    {
        $notes = DeploymentPipelineBuilder::buildNotes([
            'status' => 'success',
            'environment' => 'production',
            'version' => 'v1.0.0',
            'triggered_by' => 'CI',
        ]);

        $this->assertCount(6, $notes['pipeline_stages']);
        $this->assertContains('done', array_column($notes['pipeline_stages'], 'status'));
    }

    public function test_demo_seeder_populates_realistic_fleet_data(): void
    {
        Project::query()->create(['name' => 'App A', 'domain' => 'a.test', 'status' => 'active']);
        Project::query()->create(['name' => 'App B', 'domain' => 'b.test', 'status' => 'active']);

        (new DeploymentDemoSeeder)->run();

        $this->assertGreaterThanOrEqual(8, ProjectDeployment::query()->count());
        $this->assertGreaterThan(0, DeploymentIntegration::query()->count());
        $this->assertGreaterThan(0, DeploymentOpsEvent::query()->count());
    }
}
