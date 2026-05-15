<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_projects_index(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_projects_operations_center(): void
    {
        $user = User::factory()->create();
        Project::query()->create([
            'name' => 'Demo SaaS',
            'domain' => 'demo.example.com',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee(__('Product & Deployment Center'))
            ->assertSee(__('Deployment center'))
            ->assertSee('Demo SaaS');
    }

    public function test_project_show_displays_deployment_intelligence(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Fleet App',
            'domain' => 'fleet.example.com',
            'status' => 'active',
            'version' => 'v2.1.0',
        ]);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee(__('Deployment pipeline'))
            ->assertSee(__('Build logs'))
            ->assertSee('Fleet App');
    }
}
