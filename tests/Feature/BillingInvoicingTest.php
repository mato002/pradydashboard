<?php

namespace Tests\Feature;

use App\Domain\Billing\DraftInvoiceGenerator;
use App\Domain\Billing\InvoicePaymentRecorder;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantProjectModuleSubscription;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingInvoicingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', [
            'vat_registered' => true,
            'tax_rate' => '16',
            'default_currency' => 'KES',
            'invoice_prefix' => 'INV',
            'default_payment_terms' => 'Net 30',
        ]);

        $this->seed(\Database\Seeders\DocumentTemplateSeeder::class);
        \App\Models\BillingAutomationRule::platform();
    }

    private function billableTenant(): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'Billing App',
            'domain' => 'bill.test',
            'currency' => 'KES',
        ]);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Billing Co',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'subscription_amount' => 10000,
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $subscription->update(['monthly_fee' => 10000, 'setup_fee' => 5000]);

        return [$user, $tenant, $subscription, $project];
    }

    public function test_invoice_generated_from_subscription_monthly_fee(): void
    {
        [$user, $tenant] = $this->billableTenant();

        $this->actingAs($user)
            ->post(route('tenants.billing.generate-draft', $tenant))
            ->assertRedirect();

        $invoice = TenantInvoice::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame('draft', $invoice->status);
        $this->assertTrue(
            $invoice->lineItems()->where('item_type', 'subscription')->exists()
        );
        $this->assertGreaterThanOrEqual(10000, (float) $invoice->subtotal);
    }

    public function test_subscribed_module_line_appears_on_invoice(): void
    {
        [$user, $tenant, $subscription, $project] = $this->billableTenant();
        $module = ProjectModule::query()->create([
            'project_id' => $project->id,
            'name' => 'Analytics',
            'code' => 'analytics',
            'monthly_price' => 2500,
        ]);
        TenantProjectModuleSubscription::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'project_module_id' => $module->id,
            'enabled' => true,
            'subscribed' => true,
            'billing_status' => 'active',
        ]);

        app(DraftInvoiceGenerator::class)->generate($tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule']));

        $this->assertTrue(
            TenantInvoiceLineItem::query()->where('item_type', 'module')->exists()
        );
    }

    public function test_setup_fee_not_duplicated_on_second_invoice(): void
    {
        [, $tenant] = $this->billableTenant();
        $generator = app(DraftInvoiceGenerator::class);

        $generator->generate($tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric']));
        $firstSetupLines = TenantInvoiceLineItem::query()->where('item_type', 'setup_fee')->count();
        $this->assertSame(1, $firstSetupLines);

        $generator->generate($tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric']));
        $allSetupLines = TenantInvoiceLineItem::query()->where('item_type', 'setup_fee')->count();
        $this->assertSame(1, $allSetupLines);
    }

    public function test_tax_calculated_from_billing_settings(): void
    {
        [, $tenant] = $this->billableTenant();
        $tenant->update(['billing_tax_exempt' => false]);

        $result = app(DraftInvoiceGenerator::class)->generate(
            $tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric'])
        );

        $invoice = $result['invoice'];
        $this->assertGreaterThan(0, (float) $invoice->tax_amount);
        $this->assertSame(
            round((float) $invoice->subtotal * 0.16, 2),
            (float) $invoice->tax_amount
        );
    }

    public function test_payment_reduces_balance(): void
    {
        [, $tenant] = $this->billableTenant();
        $result = app(DraftInvoiceGenerator::class)->generate(
            $tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric'])
        );
        $invoice = $result['invoice'];
        $total = $invoice->invoiceTotal();

        app(InvoicePaymentRecorder::class)->record($invoice, [
            'amount' => $total,
            'payment_date' => now()->toDateString(),
            'method' => 'bank_transfer',
        ]);

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertLessThan(0.01, $invoice->balanceDue());
    }

    public function test_billing_summary_uses_real_subscription_mrr_only(): void
    {
        [, $tenant, $subscription] = $this->billableTenant();
        $subscription->update(['monthly_fee' => 7500]);

        TenantProjectSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => Project::query()->create(['name' => 'Suspended', 'domain' => 's.test'])->id,
            'package_name' => 'X',
            'monthly_fee' => 99999,
            'license_status' => 'suspended',
            'product_status' => 'active',
        ]);

        $summary = app(\App\Domain\Billing\BillingSummary::class)->forTenant($tenant->fresh(['projectSubscriptions']));

        $this->assertSame(7500.0, $summary['mrr']);
    }

    public function test_suspended_subscription_excluded_from_draft_invoice(): void
    {
        [, $tenant, $subscription] = $this->billableTenant();
        $subscription->update(['license_status' => 'suspended']);

        $result = app(DraftInvoiceGenerator::class)->generate(
            $tenant->fresh(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric'])
        );

        $this->assertNull($result);
    }

    public function test_tenant_billing_tab_shows_profile_form(): void
    {
        [$user, $tenant] = $this->billableTenant();

        $this->actingAs($user)
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing']))
            ->assertOk()
            ->assertSee(__('Billing profile'))
            ->assertSee(__('Generate draft invoice'));
    }
}
