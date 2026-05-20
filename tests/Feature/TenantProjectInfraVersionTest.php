<?php

namespace Tests\Feature;

use App\Domain\Tenancy\ProjectVersionRolloutSummary;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\ProjectVersion;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantProjectInfraVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_infrastructure_record_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create(['name' => 'Node A', 'status' => 'online']);
        $project = Project::query()->create(['name' => 'Infra App', 'domain' => 'infra.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Infra Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.infrastructure.update', [$tenant, $subscription]), [
                'server_id' => $server->id,
                'cpanel_account' => 'acct01',
                'domain' => 'tenant.infra.test',
                'public_url' => 'https://tenant.infra.test',
                'ssl_status' => 'valid',
                'backup_status' => 'ok',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_project_infrastructure', [
            'tenant_project_subscription_id' => $subscription->id,
            'server_id' => $server->id,
            'cpanel_account' => 'acct01',
            'domain' => 'tenant.infra.test',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.infrastructure.update', [$tenant, $subscription]), [
                'server_id' => $server->id,
                'cpanel_account' => 'acct02',
                'backup_status' => 'warning',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_project_infrastructure', [
            'tenant_project_subscription_id' => $subscription->id,
            'cpanel_account' => 'acct02',
            'backup_status' => 'warning',
        ]);
    }

    public function test_version_tracking_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Version App', 'domain' => 'ver.test', 'version' => '2.0.0']);
        ProjectVersion::query()->create([
            'project_id' => $project->id,
            'version' => '2.0.0',
            'is_current' => true,
        ]);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Version Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.version.update', [$tenant, $subscription]), [
                'current_version' => '1.5.0',
                'latest_version' => '2.0.0',
                'update_status' => 'outdated',
                'commit_hash' => 'abc123',
            ])
            ->assertRedirect();

        $row = TenantProjectVersion::query()->where('tenant_project_subscription_id', $subscription->id)->first();
        $this->assertNotNull($row);
        $this->assertSame('1.5.0', $row->current_version);
        $this->assertSame('outdated', $row->update_status);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.version.update', [$tenant, $subscription]), [
                'current_version' => '2.0.0',
                'latest_version' => '2.0.0',
                'update_status' => 'latest',
            ])
            ->assertRedirect();

        $row->refresh();
        $this->assertSame('2.0.0', $row->current_version);
        $this->assertSame('latest', $row->update_status);
    }

    public function test_project_rollout_summary_counts_tenant_versions(): void
    {
        $project = Project::query()->create(['name' => 'Rollout', 'domain' => 'roll.test', 'version' => '3.0.0']);
        ProjectVersion::query()->create(['project_id' => $project->id, 'version' => '3.0.0', 'is_current' => true]);

        $tenantA = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'A',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        $tenantB = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'B',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenantA);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenantB);

        $subA = TenantProjectSubscription::query()->where('tenant_id', $tenantA->id)->firstOrFail();
        $subB = TenantProjectSubscription::query()->where('tenant_id', $tenantB->id)->firstOrFail();

        TenantProjectVersion::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $subA->id],
            ['current_version' => '3.0.0', 'latest_version' => '3.0.0', 'update_status' => 'latest']
        );
        TenantProjectVersion::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $subB->id],
            ['current_version' => '2.0.0', 'latest_version' => '3.0.0', 'update_status' => 'outdated']
        );

        $project->load('tenantProjectSubscriptions.versionTracking');
        $summary = (new ProjectVersionRolloutSummary)->forProject($project);

        $this->assertSame(2, $summary['total']);
        $this->assertSame(1, $summary['latest']);
        $this->assertSame(1, $summary['outdated']);
        $this->assertSame('3.0.0', $summary['project_current_version']);
    }

    public function test_infrastructure_and_version_empty_states(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Empty Ops', 'domain' => 'empty-ops.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Empty Ops Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=infrastructure')
            ->assertOk()
            ->assertSee(__('No project subscriptions for this tenant'));

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=versions')
            ->assertOk()
            ->assertSee(__('No project subscriptions for this tenant'));

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=infrastructure')
            ->assertOk()
            ->assertSee(__('Save infrastructure'));

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=versions')
            ->assertOk()
            ->assertSee(__('Unknown'))
            ->assertSee(__('Save version tracking'));
    }
}
