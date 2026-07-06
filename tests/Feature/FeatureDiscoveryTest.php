<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_discovery_search_returns_matching_navigation_items(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('feature-discovery.search', ['q' => 'invoice']));

        $response->assertOk();
        $response->assertJsonStructure(['results' => [['id', 'label', 'path', 'url', 'category']]]);

        $labels = collect($response->json('results'))->pluck('label')->all();

        $this->assertTrue(
            collect($labels)->contains(fn (string $label) => str_contains(strtolower($label), 'invoice')),
            'Expected at least one invoice-related navigation result.',
        );
    }

    public function test_feature_discovery_search_respects_rbac_with_active_super_admin(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $this->seed(RbacBootstrapSeeder::class);

        $assignment = UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->where('code', config('rbac.super_admin_role_code') ?: 'super_admin'))
            ->first();

        $this->assertNotNull($assignment, 'Super admin assignment should exist after bootstrap.');

        UserActiveRole::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_role_assignment_id' => $assignment->id,
                'activated_at' => now(),
                'elevation_verified_at' => now(),
                'session_id' => null,
            ],
        );

        $response = $this->actingAs($user)
            ->getJson(route('feature-discovery.search', ['q' => 'invoice']));

        $response->assertOk();

        $labels = collect($response->json('results'))->pluck('label')->all();

        $this->assertTrue(
            collect($labels)->contains(fn (string $label) => str_contains(strtolower($label), 'invoice')),
            'Expected invoice navigation for elevated super admin. Got: '.implode(', ', $labels),
        );
    }

    public function test_feature_discovery_search_respects_session_bound_active_role(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $this->seed(RbacBootstrapSeeder::class);

        $assignment = UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->where('code', config('rbac.super_admin_role_code') ?: 'super_admin'))
            ->firstOrFail();

        $this->actingAs($user);

        UserActiveRole::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_role_assignment_id' => $assignment->id,
                'activated_at' => now(),
                'elevation_verified_at' => now(),
                'session_id' => session()->getId(),
            ],
        );

        $response = $this->getJson(route('feature-discovery.search', ['q' => 'invoice']));

        $response->assertOk();

        $labels = collect($response->json('results'))->pluck('label')->all();

        $this->assertTrue(
            collect($labels)->contains(fn (string $label) => str_contains(strtolower($label), 'invoice')),
            'Expected invoice navigation with session-bound active role. Got: '.implode(', ', $labels),
        );
    }

    public function test_feature_discovery_search_requires_authentication(): void
    {
        $this->getJson(route('feature-discovery.search', ['q' => 'invoice']))
            ->assertUnauthorized();
    }

    public function test_feature_discovery_search_activates_preferred_role_when_missing(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $this->seed(RbacBootstrapSeeder::class);

        $assignment = UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->where('code', config('rbac.super_admin_role_code') ?: 'super_admin'))
            ->firstOrFail();

        UserActiveRole::query()->where('user_id', $user->id)->delete();

        $this->actingAs($user)
            ->getJson(route('feature-discovery.search', ['q' => 'invoice']))
            ->assertOk();

        $this->assertDatabaseHas('user_active_roles', [
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
        ]);
    }

    public function test_dashboard_includes_command_palette_trigger(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('openPalette()', false)
            ->assertSee('Search tenants, invoices, servers, settings, features', false)
            ->assertSee('__pradyFeatureDiscovery', false)
            ->assertSee('Backups', false);
    }
}
