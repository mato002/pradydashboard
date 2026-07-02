<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationSetupGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_setup_guide_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('integration-setup-guide.index'))
            ->assertOk()
            ->assertSee(__('Integration Setup Guide'))
            ->assertSee(__('End-to-end checklist'));
    }

    public function test_integration_setup_guide_accepts_section_query(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('integration-setup-guide.index', ['section' => 'license']))
            ->assertOk()
            ->assertSee(__('4. Product app — license enforcement'));
    }
}
