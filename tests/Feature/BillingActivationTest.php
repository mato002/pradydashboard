<?php

namespace Tests\Feature;

use App\Domain\Billing\PaymentAutoReconciliationService;
use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Billing\PaymentReconciliationService;
use App\Domain\Billing\TenantBillingActivationService;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Jobs\Billing\ActivatePaymentsGatewayTenantJob;
use App\Models\BillingAutomationRule;
use App\Models\Setting;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use App\Models\TenantSubscription;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class BillingActivationTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', ['default_currency' => 'KES']);
        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
    }

    public function test_paid_invoice_activates_suspended_tenant_and_extends_renewal(): void
    {
        Bus::fake([ActivatePaymentsGatewayTenantJob::class]);

        [, , $tenant, $projectSub] = $this->createTenantWithSubscription('Activate Co', [
            'status' => 'suspended',
            'renewal_date' => now()->subDays(10),
            'billing_cycle' => 'monthly',
            'payments_gateway_tenant_uuid' => 'pg-uuid-123',
        ]);

        $subscription = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_name' => 'Standard',
            'amount' => 10000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->subDays(10),
            'status' => 'suspended',
            'auto_renew' => false,
        ]);

        $invoice = $this->createOpenInvoice($tenant, 10000, 'INV-ACT-001', $subscription);

        app(PaymentRecorderService::class)->recordForInvoice($invoice, [
            'amount' => 10000,
            'payment_date' => now(),
            'method' => 'mpesa',
            'reference' => 'MPESA123',
        ]);

        $tenant->refresh();
        $subscription->refresh();
        $invoice->refresh();
        $projectSub->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('active', $tenant->status);
        $this->assertTrue($tenant->renewal_date->isFuture());
        $this->assertSame('active', $subscription->status);
        $this->assertSame('active', $projectSub->fresh()->license_status);

        Bus::assertDispatched(ActivatePaymentsGatewayTenantJob::class);
    }

    public function test_auto_reconcile_by_invoice_reference_triggers_activation(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Reconcile Co', [
            'status' => 'overdue',
            'renewal_date' => now()->subDays(3),
        ]);

        $invoice = $this->createOpenInvoice($tenant, 5000, 'INV-REF-900');

        $payment = TenantPayment::query()->create([
            'transaction_id' => 'TXN-AUTO-1',
            'tenant_id' => $tenant->id,
            'amount' => 5000,
            'unapplied_amount' => 5000,
            'currency' => 'KES',
            'status' => 'successful',
            'reconciliation_status' => 'unreconciled',
            'paid_at' => now(),
            'method' => 'mpesa',
            'reference' => 'INV-REF-900',
        ]);

        $matched = app(PaymentAutoReconciliationService::class)->tryReconcile($payment);

        $this->assertTrue($matched);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('active', $tenant->fresh()->status);
    }

    public function test_reconciliation_match_activates_tenant(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Match Co', [
            'status' => 'suspended',
            'renewal_date' => now()->subWeek(),
        ]);

        $invoice = $this->createOpenInvoice($tenant, 2500, 'INV-MATCH-01');

        $payment = TenantPayment::query()->create([
            'transaction_id' => 'TXN-MATCH-1',
            'tenant_id' => $tenant->id,
            'amount' => 2500,
            'unapplied_amount' => 2500,
            'currency' => 'KES',
            'status' => 'successful',
            'reconciliation_status' => 'unreconciled',
            'paid_at' => now(),
            'method' => 'bank',
        ]);

        app(PaymentReconciliationService::class)->matchToInvoice($payment, $invoice);

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_reverse_after_activation_rolls_back_entitlement(): void
    {
        Bus::fake([ActivatePaymentsGatewayTenantJob::class]);

        [, , $tenant, $projectSub] = $this->createTenantWithSubscription('Reverse Co', [
            'status' => 'suspended',
            'renewal_date' => now()->subDays(10),
            'billing_cycle' => 'monthly',
        ]);

        $subscription = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_name' => 'Standard',
            'amount' => 8000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->subDays(10),
            'status' => 'suspended',
            'auto_renew' => false,
        ]);

        $periodEndBeforePay = $subscription->current_period_end->copy();

        $invoice = $this->createOpenInvoice($tenant, 8000, 'INV-REV-ACT-01', $subscription);

        $payment = app(PaymentRecorderService::class)->recordForInvoice($invoice, [
            'amount' => 8000,
            'payment_date' => now(),
            'method' => 'mpesa',
            'reference' => 'REVACT001',
        ]);

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertSame('active', $subscription->fresh()->status);
        $extendedEnd = $subscription->fresh()->current_period_end->copy();
        $this->assertTrue($extendedEnd->gt($periodEndBeforePay));

        app(PaymentReconciliationService::class)->reverse($payment->fresh());

        $tenant->refresh();
        $subscription->refresh();
        $invoice->refresh();
        $projectSub->refresh();

        $this->assertNotSame('paid', $invoice->status);
        $this->assertSame('overdue', $tenant->status);
        $this->assertSame('overdue', $subscription->status);
        $this->assertTrue($subscription->current_period_end->lt($extendedEnd));
        $this->assertSame('restricted', $tenant->latestAccessControl?->level);
        $this->assertTrue(
            \App\Models\SystemActivityLog::query()
                ->where('action', 'tenant.deactivated_from_payment_reverse')
                ->exists()
        );
    }

    public function test_paying_upgrade_invoice_activates_new_plan_and_cancels_prior(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Upgrade Pay Co', [
            'status' => 'active',
            'subscription_plan' => 'Starter',
            'subscription_amount' => 5000,
            'billing_cycle' => 'monthly',
        ]);

        $old = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_name' => 'Starter',
            'product_name' => 'CRM',
            'amount' => 5000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->subMonth()->toDateString(),
            'current_period_end' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $periodEnd = now()->addMonth()->toDateString();
        $pending = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_name' => 'Professional',
            'product_name' => 'CRM',
            'amount' => 15000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->toDateString(),
            'current_period_end' => $periodEnd,
            'status' => 'pending',
            'auto_renew' => false,
            'replaces_subscription_id' => $old->id,
        ]);

        $invoice = $this->createOpenInvoice($tenant, 15000, 'INV-UPG-01', $pending);

        app(PaymentRecorderService::class)->recordForInvoice($invoice, [
            'amount' => 15000,
            'payment_date' => now(),
            'method' => 'mpesa',
            'reference' => 'UPG001',
        ]);

        $old->refresh();
        $pending->refresh();
        $tenant->refresh();

        $this->assertSame('cancelled', $old->status);
        $this->assertSame('active', $pending->status);
        $this->assertTrue($pending->auto_renew);
        $this->assertSame('Professional', $tenant->subscription_plan);
        $this->assertEquals(15000, (float) $tenant->subscription_amount);
        $this->assertSame('active', $tenant->status);
        $this->assertSame($periodEnd, $pending->current_period_end->toDateString());
    }

    private function createOpenInvoice($tenant, float $total, string $number, ?TenantSubscription $subscription = null): TenantInvoice
    {
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => $number,
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => $total,
            'total' => $total,
            'amount_due' => $total,
            'status' => 'sent',
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'is_recurring' => $subscription !== null,
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => $subscription ? 'subscription' : 'custom',
            'description' => 'Service fee',
            'quantity' => 1,
            'unit_price' => $total,
            'line_total' => $total,
            'related_model_type' => $subscription ? TenantSubscription::class : null,
            'related_model_id' => $subscription?->id,
        ]);

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant->fresh());

        return $invoice->fresh(['lineItems', 'tenant']);
    }
}
