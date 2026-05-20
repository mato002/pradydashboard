<?php

namespace Tests\Feature;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Billing\DraftInvoiceGenerator;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\StaffProfile;
use App\Models\SupportTicket;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\Server;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use App\Support\ActivityLogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Tenant, 2: TenantProjectSubscription, 3: Project}
     */
    private function tenantFixture(): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Audit App', 'domain' => 'audit.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Audit Co',
            'status' => 'active',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $subscription->update(['monthly_fee' => 5000]);

        return [$user, $tenant, $subscription, $project];
    }

    public function test_invoice_generation_logs_activity(): void
    {
        [$user, $tenant] = $this->tenantFixture();

        $this->actingAs($user)
            ->post(route('tenants.billing.generate-draft', $tenant))
            ->assertRedirect();

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'invoice.draft_generated',
            'category' => ActivityLogCategory::BILLING,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_license_suspension_logs_activity(): void
    {
        [$user, $tenant, $subscription] = $this->tenantFixture();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.license.suspend', [$tenant, $subscription]))
            ->assertRedirect();

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'license.suspended',
            'category' => ActivityLogCategory::LICENSE,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_support_ticket_comment_logs_activity(): void
    {
        [$user, $tenant] = $this->tenantFixture();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $tenant->project_id,
            'subject' => 'Login issue',
            'category' => 'bug',
            'priority' => 'high',
            'status' => 'open',
            'source' => 'email',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('tenants.support-tickets.comments.store', [$tenant, $ticket]), [
                'message' => 'Checking logs',
                'comment_type' => 'internal_note',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'support.comment_added',
            'category' => ActivityLogCategory::SUPPORT,
            'support_ticket_id' => $ticket->id,
        ]);
    }

    public function test_integration_secret_is_masked_in_activity_log(): void
    {
        [$user, $tenant, $subscription] = $this->tenantFixture();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.store', [$tenant, $subscription]), [
                'integration_category' => 'provider',
                'service_type' => 'bulk_sms',
                'display_name' => 'Africa SMS',
                'endpoint_url' => 'https://api.sms.test',
                'api_secret' => 'super-secret-key-12345',
            ])
            ->assertRedirect();

        $log = SystemActivityLog::query()
            ->where('action', 'integration.created')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $encoded = json_encode($log->new_values);
        $this->assertStringNotContainsString('super-secret-key-12345', (string) $encoded);
        $this->assertStringContainsString('***MASKED***', (string) $encoded);
    }

    public function test_infrastructure_and_version_updates_log_activity(): void
    {
        [$user, $tenant, $subscription] = $this->tenantFixture();
        $server = Server::query()->create(['name' => 'Log Node', 'status' => 'online']);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.infrastructure.update', [$tenant, $subscription]), [
                'server_id' => $server->id,
                'domain' => 'log.test',
                'ssl_status' => 'valid',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'tenant.infrastructure_updated',
            'category' => ActivityLogCategory::TENANT,
            'tenant_id' => $tenant->id,
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.version.update', [$tenant, $subscription]), [
                'current_version' => '1.0.0',
                'latest_version' => '2.0.0',
                'update_status' => 'outdated',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'tenant.version_tracking_updated',
            'category' => ActivityLogCategory::TENANT,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_global_activity_page_filters_by_tenant_and_category(): void
    {
        $user = User::factory()->create();
        $logger = app(ActivityLogger::class);

        $tenantA = Tenant::query()->create([
            'project_id' => Project::query()->create(['name' => 'A', 'domain' => 'a.test'])->id,
            'company_name' => 'Tenant A',
            'status' => 'active',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
        ]);

        $tenantB = Tenant::query()->create([
            'project_id' => Project::query()->create(['name' => 'B', 'domain' => 'b.test'])->id,
            'company_name' => 'Tenant B',
            'status' => 'active',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
        ]);

        $logger->log('tenant.updated', ActivityLogCategory::TENANT, 'Tenant A change', $tenantA);
        $logger->log('invoice.draft_generated', ActivityLogCategory::BILLING, 'Tenant B invoice', null, null, null, [
            'tenant_id' => $tenantB->id,
        ]);

        $query = app(ActivityLogQuery::class);
        $tenantAOnly = $query->apply(SystemActivityLog::query(), [
            'tenant_id' => (string) $tenantA->id,
        ])->get();

        $this->assertTrue($tenantAOnly->contains('description', 'Tenant A change'));
        $this->assertFalse($tenantAOnly->contains('description', 'Tenant B invoice'));

        $billingOnly = $query->apply(SystemActivityLog::query(), [
            'category' => ActivityLogCategory::BILLING,
        ])->get();

        $this->assertTrue($billingOnly->every(fn ($log) => $log->category === ActivityLogCategory::BILLING));

        $this->actingAs($user)
            ->get(route('activity-logs.index', [
                'tenant_id' => $tenantA->id,
                'category' => ActivityLogCategory::TENANT,
            ]))
            ->assertOk()
            ->assertSee('Tenant A change')
            ->assertDontSee('Tenant B invoice');
    }
}
