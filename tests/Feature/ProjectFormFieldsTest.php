<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hosted_project_store_persists_core_fields(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Unified Product',
            'slug' => 'unified-product',
            'status' => 'active',
        ]);

        $payload = [
            'product_id' => $product->id,
            'name' => 'Unified Instance',
            'domain' => 'unified.example.com',
            'environment' => 'production',
            'status' => 'active',
            'description' => 'Hosted instance',
            'notes' => 'General notes',
        ];

        $this->actingAs($user)
            ->post(route('hosted-projects.store'), $payload)
            ->assertRedirect(route('hosted-projects.index'));

        $this->assertDatabaseHas('hosted_projects', [
            'name' => 'Unified Instance',
            'domain' => 'unified.example.com',
            'product_id' => $product->id,
            'environment' => 'production',
            'status' => 'active',
        ]);
    }

    public function test_hosted_project_edit_form_is_accessible(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Form Product',
            'slug' => 'form-product',
            'status' => 'active',
        ]);
        $project = Project::query()->create([
            'product_id' => $product->id,
            'name' => 'Form Instance',
            'domain' => 'form.example.com',
            'environment' => 'production',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('hosted-projects.edit', $project))
            ->assertOk()
            ->assertSee(__('Form Instance'));
    }
}
