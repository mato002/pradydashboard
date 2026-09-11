<?php

namespace Tests\Feature;

use App\Domain\Billing\FinancialOperationsQuery;
use App\Domain\Rbac\RbacScopeFilter;
use App\Jobs\Billing\ReconcilePaymentsBatchJob;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\User;
use App\Support\Billing\PaymentReconciliationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class PaymentReconciliationSummaryTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    public function test_run_reconciliation_flash_describes_auto_match_batch(): void
    {
        Bus::fake([ReconcilePaymentsBatchJob::class]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('payments.reconcile'))
            ->assertRedirect(route('payments.index'))
            ->assertSessionHas('status');

        $status = session('status');
        $this->assertIsString($status);
        $this->assertStringContainsString('50', $status);
        $this->assertStringContainsString('unreconciled', strtolower($status));
        $this->assertStringNotContainsString('settlement batches', strtolower($status));

        Bus::assertDispatched(ReconcilePaymentsBatchJob::class);
    }

    public function test_reconciliation_summary_uses_reconciliation_status_not_payment_status(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Recon Summary Co');

        TenantPayment::query()->create([
            'transaction_id' => 'TXN-MATCHED-1',
            'tenant_id' => $tenant->id,
            'amount' => 1000,
            'currency' => 'KES',
            'status' => 'successful',
            'reconciliation_status' => PaymentReconciliationStatus::MATCHED,
            'paid_at' => now(),
            'matched_at' => now()->subHour(),
        ]);

        TenantPayment::query()->create([
            'transaction_id' => 'TXN-UNREC-1',
            'tenant_id' => $tenant->id,
            'amount' => 2000,
            'unapplied_amount' => 2000,
            'currency' => 'KES',
            'status' => 'successful',
            'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
            'paid_at' => now(),
        ]);

        TenantPayment::query()->create([
            'transaction_id' => 'TXN-PENDING-1',
            'tenant_id' => $tenant->id,
            'amount' => 500,
            'currency' => 'KES',
            'status' => 'pending',
            'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
        ]);

        Cache::put(ReconcilePaymentsBatchJob::LAST_RUN_CACHE_KEY, [
            'at' => now()->subMinutes(5)->toIso8601String(),
            'processed' => 3,
            'matched' => 1,
        ], now()->addHour());

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('payments.index'));
        $response->assertOk();

        $reconciliation = $response->viewData('reconciliation');

        $this->assertSame(1, $reconciliation['matched']);
        $this->assertSame(1, $reconciliation['pending']); // awaiting match (successful unreconciled)
        $this->assertSame(1, $reconciliation['unallocated']);
        $this->assertSame(50.0, $reconciliation['match_rate']);
        $this->assertStringContainsString('1/3', $reconciliation['last_run']);
        $this->assertStringContainsString('50', $reconciliation['settlement_window']);
        $this->assertStringNotContainsString('T+1', $reconciliation['settlement_window']);
    }

    public function test_zero_total_invoice_is_not_marked_paid(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Zero Inv Co');

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-ZERO-001',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 0,
            'total' => 0,
            'amount_due' => 0,
            'amount_paid' => 0,
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        $invoice->syncPaymentStatus();
        $invoice->save();

        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertNotSame('paid', $invoice->fresh()->status);
    }

    public function test_overview_paid_kpi_excludes_zero_total_paid_rows(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('KPI Co');

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-KPI-ZERO',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 0,
            'total' => 0,
            'amount_due' => 0,
            'amount_paid' => 0,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-KPI-REAL',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 0,
            'amount_paid' => 5000,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $kpis = app(FinancialOperationsQuery::class)->overviewKpis(app(RbacScopeFilter::class));

        $this->assertSame(1, $kpis['paid']);
    }

    public function test_gateway_filter_scopes_kpis_and_reconciliation(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Gateway Filter Co');

        TenantPayment::query()->create([
            'transaction_id' => 'TXN-MPESA-OK',
            'tenant_id' => $tenant->id,
            'amount' => 3000,
            'currency' => 'KES',
            'status' => 'successful',
            'gateway' => 'mpesa',
            'reconciliation_status' => PaymentReconciliationStatus::MATCHED,
            'paid_at' => now(),
            'matched_at' => now(),
        ]);

        TenantPayment::query()->create([
            'transaction_id' => 'TXN-STRIPE-OK',
            'tenant_id' => $tenant->id,
            'amount' => 9000,
            'currency' => 'KES',
            'status' => 'successful',
            'gateway' => 'stripe',
            'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
            'unapplied_amount' => 9000,
            'paid_at' => now(),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('payments.index', ['gateway' => 'mpesa']));
        $response->assertOk();
        $response->assertSee(__('Payments — :gateway', ['gateway' => 'M-Pesa']));
        $response->assertSee(__('Not measured'));
        $response->assertDontSee('+2', false);

        $kpis = $response->viewData('kpis');
        $reconciliation = $response->viewData('reconciliation');

        $this->assertSame(3000.0, $kpis['collectedRaw']);
        $this->assertSame(1, $reconciliation['matched']);
        $this->assertSame(0, $reconciliation['pending']);
    }
}
