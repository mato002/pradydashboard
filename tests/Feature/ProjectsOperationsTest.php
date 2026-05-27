<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_hosted_projects_index(): void
    {
        $this->get(route('hosted-projects.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_hosted_projects_operations_center(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Demo Product',
            'slug' => 'demo-product',
            'status' => 'active',
        ]);
        Project::query()->create([
            'product_id' => $product->id,
            'name' => 'Demo SaaS',
            'domain' => 'demo.example.com',
            'environment' => 'production',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('hosted-projects.index'))
            ->assertOk()
            ->assertSee(__('Hosted Projects'))
            ->assertSee('Demo SaaS');
    }

    public function test_hosted_project_show_displays_deployment_intelligence(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Fleet Product',
            'slug' => 'fleet-product',
            'status' => 'active',
        ]);
        $project = Project::query()->create([
            'product_id' => $product->id,
            'name' => 'Fleet App',
            'domain' => 'fleet.example.com',
            'environment' => 'production',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('hosted-projects.show', $project))
            ->assertOk()
            ->assertSee(__('Deployment pipeline'))
            ->assertSee('Fleet App');
    }

    public function test_legacy_projects_url_redirects_to_hosted_projects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/projects')
            ->assertRedirect('/hosted-projects');
    }
}
