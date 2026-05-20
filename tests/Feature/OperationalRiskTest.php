<?php

namespace Tests\Feature;

use App\Domain\Operations\OperationalRiskScanner;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\OperationalRiskAcknowledgement;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalRiskTest extends TestCase
{
    use RefreshDatabase;

    private function tenantWithSubscription(): array
    {
        $project = Project::query()->create([
            'name' => 'Risk App',
            'domain' => 'risk.test',
            'contract_document_required' => true,
        ]);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Risk Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        return [$tenant, $subscription, $project];
    }

    public function test_overdue_invoice_appears_in_scanner(): void
    {
        [$tenant] = $this->tenantWithSubscription();

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-9001',
            'status' => 'overdue',
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_paid' => 0,
            'due_date' => now()->subDays(5),
            'issue_date' => now()->subDays(35)->toDateString(),
        ]);

        $keys = app(OperationalRiskScanner::class)->scan()->pluck('key');

        $this->assertTrue($keys->contains(fn ($k) => str_starts_with((string) $k, 'invoice_overdue:')));
    }

    public function test_expiring_ssl_appears_in_scanner(): void
    {
        [$tenant, $subscription] = $this->tenantWithSubscription();

        TenantProjectInfrastructure::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'domain' => 'app.risk.test',
            'ssl_expiry_date' => now()->addDays(10),
        ]);

        $risks = app(OperationalRiskScanner::class)->scan();

        $this->assertTrue($risks->contains(fn (array $r) => str_starts_with($r['key'], 'tenant_ssl:')));
    }

    public function test_missing_contract_appears_in_scanner(): void
    {
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $keys = app(OperationalRiskScanner::class)->scan()->pluck('key');

        $this->assertTrue($keys->contains('missing_contract:'.$subscription->id));
    }

    public function test_failed_integration_appears_in_scanner(): void
    {
        [$tenant, $subscription] = $this->tenantWithSubscription();

        TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'service_type' => 'bulk_sms',
            'display_name' => 'SMS Gateway',
            'status' => 'error',
            'last_test_status' => 'fail',
            'last_error' => 'Connection refused',
        ]);

        $risks = app(OperationalRiskScanner::class)->scan();

        $this->assertTrue($risks->contains(fn (array $r) => str_starts_with($r['key'], 'integration_failed:')));
    }

    public function test_outdated_deployment_appears_in_scanner(): void
    {
        [$tenant, $subscription] = $this->tenantWithSubscription();

        TenantProjectVersion::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'current_version' => '1.0.0',
            'latest_version' => '2.0.0',
            'update_status' => 'outdated',
        ]);

        $risks = app(OperationalRiskScanner::class)->scan();

        $this->assertTrue($risks->contains(fn (array $r) => str_starts_with($r['key'], 'deployment_outdated:')));
    }

    public function test_acknowledged_risk_is_muted_in_attention_required(): void
    {
        [$tenant] = $this->tenantWithSubscription();

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-ACK',
            'status' => 'overdue',
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_paid' => 0,
            'due_date' => now()->subDay(),
            'issue_date' => now()->subDays(30)->toDateString(),
        ]);

        $invoice = TenantInvoice::query()->where('invoice_number', 'INV-ACK')->firstOrFail();
        $key = 'invoice_overdue:'.$invoice->id;

        OperationalRiskAcknowledgement::query()->create([
            'risk_key' => $key,
            'acknowledged_at' => now(),
        ]);

        $attention = app(OperationalRiskScanner::class)->attentionRequired(20);

        $this->assertFalse($attention->contains('key', $key));

        $all = app(OperationalRiskScanner::class)->scan(['acknowledged' => '']);
        $acknowledged = $all->firstWhere('key', $key);
        $this->assertNotNull($acknowledged);
        $this->assertTrue($acknowledged['acknowledged']);
    }

    public function test_risk_center_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('risk-center.index'))
            ->assertOk()
            ->assertSee(__('Risk Center'));
    }
}
