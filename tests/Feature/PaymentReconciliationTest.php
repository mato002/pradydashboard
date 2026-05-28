<?php

namespace Tests\Feature;

use App\Domain\Billing\PaymentMatchingSuggester;
use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Billing\PaymentReconciliationService;
use App\Domain\Billing\PdfGenerator;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\PaymentAllocation;
use App\Models\Setting;
use App\Models\SystemActivityLog;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', ['default_currency' => 'KES']);
        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
        Storage::fake('local');
    }

    private function tenantWithInvoice(float $total = 10000, string $number = 'INV-PAY-001'): array
    {
        [, , $tenant] = $this->createTenantWithSubscription(
            'Pay Tenant',
            [
                'billing_email' => 'pay@tenant.test',
                'billing_phone' => '254700000001',
            ],
            ['name' => 'Pay Co', 'domain' => 'pay.test'],
        );

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => $number,
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => $total,
            'total' => $total,
            'amount_due' => $total,
            'status' => 'sent',
            'issue_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => $total,
            'line_total' => $total,
        ]);

        return [$tenant, $invoice->fresh(['lineItems', 'tenant'])];
    }

    private function stubPdf(): void
    {
        $mock = \Mockery::mock(PdfGenerator::class);
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('store')->andReturnUsing(function ($html, $path) {
            Storage::disk('local')->put($path, '%PDF');

            return $path;
        });
        $this->app->instance(PdfGenerator::class, $mock);
    }

    public function test_unreconciled_payment_creation(): void
    {
        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'payer_name' => 'John Doe',
            'amount' => 1500,
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
            'reference' => 'QHX123',
        ]);

        $this->assertSame('unreconciled', $payment->reconciliation_status);
        $this->assertNull($payment->tenant_invoice_id);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'payment.recorded')->exists());
    }

    public function test_payment_matched_to_invoice(): void
    {
        Mail::fake();
        $this->stubPdf();
        [, $invoice] = $this->tenantWithInvoice();

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $invoice->tenant_id,
            'payer_name' => 'Pay Tenant',
            'amount' => 10000,
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
            'reference' => $invoice->invoice_number,
        ]);

        app(PaymentReconciliationService::class)->matchToInvoice($payment, $invoice);

        $invoice->refresh();
        $payment->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertContains($payment->reconciliation_status, ['matched', 'partially_matched']);
        $this->assertTrue(PaymentAllocation::query()->where('tenant_payment_id', $payment->id)->exists());
        $this->assertTrue(SystemActivityLog::query()->where('action', 'payment.matched')->exists());
    }

    public function test_partial_payment(): void
    {
        [, $invoice] = $this->tenantWithInvoice(10000);

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $invoice->tenant_id,
            'amount' => 4000,
            'payment_date' => now()->toDateString(),
            'source' => 'cash',
        ]);

        app(PaymentReconciliationService::class)->matchToInvoice($payment, $invoice, 4000);

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertEqualsWithDelta(4000, (float) $invoice->amount_paid, 0.02);
    }

    public function test_overpayment_handling(): void
    {
        [, $invoice] = $this->tenantWithInvoice(5000, 'INV-OVER-001');

        $payment = app(PaymentRecorderService::class)->recordForInvoice($invoice, [
            'amount' => 6000,
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
            'method' => 'bank_transfer',
        ]);

        $this->assertGreaterThan(0, (float) $payment->unapplied_amount);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_duplicate_detection(): void
    {
        app(PaymentRecorderService::class)->recordUnreconciled([
            'amount' => 2000,
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
            'reference' => 'DUP-REF-99',
        ]);

        $second = app(PaymentRecorderService::class)->recordUnreconciled([
            'amount' => 2000,
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
            'reference' => 'DUP-REF-99',
        ]);

        $duplicate = app(PaymentReconciliationService::class)->findDuplicate($second);
        $this->assertNotNull($duplicate);

        app(PaymentReconciliationService::class)->markDuplicate($second);
        $this->assertSame('duplicate', $second->fresh()->reconciliation_status);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'payment.duplicate_flagged')->exists());
    }

    public function test_split_payment(): void
    {
        [, $inv1] = $this->tenantWithInvoice(5000, 'INV-SPLIT-1');
        $inv2 = TenantInvoice::query()->create([
            'tenant_id' => $inv1->tenant_id,
            'invoice_number' => 'INV-SPLIT-2',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 3000,
            'total' => 3000,
            'amount_due' => 3000,
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $inv1->tenant_id,
            'amount' => 8000,
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
        ]);

        app(PaymentReconciliationService::class)->splitAcrossInvoices($payment, [
            ['invoice_id' => $inv1->id, 'amount' => 5000],
            ['invoice_id' => $inv2->id, 'amount' => 3000],
        ]);

        $this->assertSame(2, PaymentAllocation::query()->where('tenant_payment_id', $payment->id)->count());
        $this->assertTrue(SystemActivityLog::query()->where('action', 'payment.split')->exists());
    }

    public function test_receipt_generated_on_full_match(): void
    {
        Mail::fake();
        $this->stubPdf();
        BillingAutomationRule::platform()->update(['auto_generate_pdf' => true]);
        [, $invoice] = $this->tenantWithInvoice(2000, 'INV-RCP-001');

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $invoice->tenant_id,
            'amount' => 2000,
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
        ]);

        $result = app(PaymentReconciliationService::class)->matchToInvoice($payment, $invoice);

        $this->assertNotNull($result['receipt']);
        $this->assertSame('receipt', $result['receipt']->document_type);
    }

    public function test_matching_suggester_scores_by_reference(): void
    {
        [, $invoice] = $this->tenantWithInvoice(7500, 'INV-SUG-001');

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $invoice->tenant_id,
            'amount' => 7500,
            'reference' => 'Payment for INV-SUG-001',
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
        ]);

        $suggestions = app(PaymentMatchingSuggester::class)->suggest($payment);
        $this->assertGreaterThan(0, $suggestions->count());
        $this->assertSame($invoice->id, $suggestions->first()['invoice']->id);
    }

    public function test_payment_reversed(): void
    {
        [, $invoice] = $this->tenantWithInvoice(3000, 'INV-REV-001');

        $payment = app(PaymentRecorderService::class)->recordForInvoice($invoice, [
            'amount' => 3000,
            'payment_date' => now()->toDateString(),
            'source' => 'cash',
        ]);

        app(PaymentReconciliationService::class)->reverse($payment->fresh());

        $payment->refresh();
        $invoice->refresh();

        $this->assertSame('unreconciled', $payment->reconciliation_status);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'payment.reversed')->exists());
    }
}
