<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_store_persists_unified_operations_fields(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Unified Product',
            'domain' => 'unified.example.com',
            'product_slug' => 'unified_product',
            'system_code' => 'unified-product',
            'description' => 'Full ops product',
            'status' => 'development',
            'version' => '2.0.0',
            'min_supported_version' => '1.5.0',
            'latest_release_date' => '2026-05-01',
            'owner_department' => 'Product Engineering',
            'internal_notes' => 'Internal only',
            'business_model' => 'saas',
            'deployment_type' => 'cloud',
            'billing_model' => 'monthly',
            'default_setup_fee' => 5000,
            'default_monthly_fee' => 12000,
            'currency' => 'KES',
            'trial_days' => 14,
            'minimum_contract_term' => 12,
            'license_validation_mode' => 'api',
            'grace_period_days' => 10,
            'kill_switch_allowed' => '1',
            'offline_mode_allowed' => '0',
            'contract_document_required' => '1',
            'requires_server' => '1',
            'requires_domain' => '1',
            'requires_ssl' => '1',
            'requires_whm' => '0',
            'default_disk_quota_mb' => 5120,
            'default_database_required' => '1',
            'backup_required' => '1',
            'notes' => 'General notes',
        ];

        $this->actingAs($user)
            ->post(route('projects.store'), $payload)
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'name' => 'Unified Product',
            'system_code' => 'unified-product',
            'business_model' => 'saas',
            'deployment_type' => 'cloud',
            'billing_model' => 'monthly',
            'license_validation_mode' => 'api',
            'currency' => 'KES',
            'contract_document_required' => 1,
            'requires_whm' => 0,
        ]);

        $project = Project::query()->where('name', 'Unified Product')->first();
        $this->assertNotNull($project);
        $this->assertSame('2.0.0', $project->version);
        $this->assertSame(14, $project->trial_days);
        $this->assertSame(5120, $project->default_disk_quota_mb);
    }

    public function test_project_edit_form_shows_new_field_sections(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Form Product',
            'domain' => 'form.example.com',
            'status' => 'active',
            'currency' => 'KES',
            'license_validation_mode' => 'api',
            'grace_period_days' => 7,
        ]);

        $this->actingAs($user)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee(__('Business model'))
            ->assertSee(__('License validation mode'))
            ->assertSee(__('Infrastructure requirements'));
    }
}
