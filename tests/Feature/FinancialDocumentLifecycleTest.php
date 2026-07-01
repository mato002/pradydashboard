<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Domain\Billing\InvoicePaymentRecorder;
use App\Domain\Billing\ProformaConverter;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Billing\ReceiptGenerator;
use App\Domain\Billing\StatementGenerator;
use App\Models\BillingAutomationRule;
use App\Models\GeneratedDocument;
use App\Models\Setting;
use App\Models\SystemActivityLog;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use App\Models\User;
use App\Support\Billing\BillingDocumentType;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class FinancialDocumentLifecycleTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', [
            'default_currency' => 'KES',
            'invoice_prefix' => 'INV',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform()->update(['auto_generate_pdf' => false]);
    }

    private function billableTenant(): array
    {
        $user = User::factory()->create();
        [, , $tenant] = $this->createTenantWithSubscription('Lifecycle Co');

        return [$user, $tenant];
    }

    private function draftInvoice(int $tenantId, float $total = 3000): TenantInvoice
    {
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenantId,
            'invoice_number' => '10/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => $total,
            'total' => $total,
            'amount_due' => $total,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service fee',
            'quantity' => 1,
            'unit_price' => $total,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => $total,
        ]);

        return $invoice->fresh(['lineItems', 'tenant']);
    }

    public function test_finalized_invoice_snapshot_does_not_change_silently(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);

        $document = app(DocumentFinalizer::class)->finalize($invoice);
        $originalTotal = $document->data_snapshot['total'];

        $invoice->update(['total' => 99999, 'subtotal' => 99999]);

        $this->assertSame($originalTotal, $document->fresh()->data_snapshot['total']);
    }

    public function test_regeneration_requires_explicit_action_and_is_guarded_when_paid(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);
        app(DocumentFinalizer::class)->finalize($invoice);

        $invoice->update(['status' => 'paid', 'amount_paid' => 3000, 'amount_due' => 0]);

        $this->expectException(\InvalidArgumentException::class);
        app(DocumentFinalizer::class)->regenerate($invoice->fresh());
    }

    public function test_regeneration_logs_activity(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);
        app(DocumentFinalizer::class)->finalize($invoice);

        app(DocumentFinalizer::class)->regenerate($invoice->fresh());

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'document.regenerated',
            'invoice_id' => $invoice->id,
        ]);
    }

    public function test_quotation_cannot_convert_twice(): void
    {
        [, $tenant] = $this->billableTenant();
        $quotation = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'Q1/26',
            'document_type' => BillingDocumentType::QUOTATION,
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'approval_status' => 'approved',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $quotation->id,
            'item_type' => 'custom',
            'description' => 'Quote line',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
        ]);

        $converter = app(QuotationConverter::class);
        $first = $converter->convert($quotation->fresh(['lineItems']));
        $second = $converter->convert($quotation->fresh());

        $this->assertSame($first->id, $second->id);
    }

    public function test_cancelled_quotation_cannot_convert(): void
    {
        [, $tenant] = $this->billableTenant();
        $quotation = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'Q2/26',
            'document_type' => BillingDocumentType::QUOTATION,
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'cancelled',
            'approval_status' => 'approved',
            'issue_date' => now()->toDateString(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(QuotationConverter::class)->convert($quotation);
    }

    public function test_proforma_cannot_convert_twice(): void
    {
        [, $tenant] = $this->billableTenant();
        $proforma = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'PF1/26',
            'document_type' => BillingDocumentType::PROFORMA,
            'currency' => 'KES',
            'subtotal' => 2000,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $proforma->id,
            'item_type' => 'custom',
            'description' => 'Proforma line',
            'quantity' => 1,
            'unit_price' => 2000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 2000,
        ]);

        $converter = app(ProformaConverter::class);
        $first = $converter->convert($proforma->fresh(['lineItems']));
        $second = $converter->convert($proforma->fresh());

        $this->assertSame($first->id, $second->id);
    }

    public function test_receipt_is_not_duplicated_for_same_payment(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);
        $invoice->update(['status' => 'sent']);

        $payment = TenantPayment::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_invoice_id' => $invoice->id,
            'amount' => 3000,
            'currency' => 'KES',
            'status' => 'successful',
            'paid_at' => now(),
            'method' => 'mpesa',
        ]);

        $invoice->update(['status' => 'paid', 'amount_paid' => 3000, 'amount_due' => 0]);

        $generator = app(ReceiptGenerator::class);
        $first = $generator->generateForPayment($invoice->fresh(), $payment);
        $second = $generator->generateForPayment($invoice->fresh(), $payment);

        $this->assertSame($first->id, $second->id);
        $this->assertSame($payment->id, $first->tenant_payment_id);
        $this->assertSame(1, TenantInvoice::query()->where('document_type', BillingDocumentType::RECEIPT)->where('tenant_payment_id', $payment->id)->count());
    }

    public function test_receipt_remains_available_after_invoice_cancelled(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);
        $invoice->update(['status' => 'sent']);

        $payment = TenantPayment::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_invoice_id' => $invoice->id,
            'amount' => 3000,
            'currency' => 'KES',
            'status' => 'successful',
            'paid_at' => now(),
            'method' => 'bank',
        ]);
        $invoice->update(['status' => 'paid', 'amount_paid' => 3000, 'amount_due' => 0]);

        $receipt = app(ReceiptGenerator::class)->generateForPayment($invoice->fresh(['tenant']), $payment);
        $invoice->update(['status' => 'cancelled']);

        $receipt = $receipt->fresh(['linkedInvoice']);
        $this->assertNotNull($receipt);
        $this->assertTrue($receipt->linkedInvoice->isCancelled());
        $this->assertSame(__('Source invoice cancelled'), $receipt->lifecycleLabel());
    }

    public function test_statement_snapshot_remains_stable_after_invoice_changes(): void
    {
        [, $tenant] = $this->billableTenant();
        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '11/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 5000,
            'status' => 'sent',
            'issue_date' => now()->startOfMonth()->toDateString(),
        ]);

        $statement = app(StatementGenerator::class)->generate($tenant);
        $document = GeneratedDocument::query()->where('tenant_invoice_id', $statement->id)->firstOrFail();
        $originalClosing = $document->data_snapshot['closing_balance'] ?? null;

        TenantInvoice::query()->where('tenant_id', $tenant->id)->where('document_type', BillingDocumentType::INVOICE)->update(['total' => 99999]);

        $rebuilt = app(DocumentSnapshotBuilder::class)->build($statement->fresh(['tenant', 'generatedDocuments']));
        $this->assertSame($originalClosing, $rebuilt['closing_balance']);
    }

    public function test_action_buttons_render_correctly_per_document_type(): void
    {
        [$user, $tenant] = $this->billableTenant();

        $receipt = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'R1/26',
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => 'KES',
            'subtotal' => 500,
            'total' => 500,
            'amount_paid' => 500,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
            'finalized_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('invoices.show', $receipt))
            ->assertOk()
            ->assertSee(__('Download PDF'), false)
            ->assertDontSee(__('Record Payment'), false)
            ->assertDontSee(__('Convert to invoice'), false);
    }

    public function test_activity_logs_written_for_lifecycle_actions(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant->id);

        app(DocumentFinalizer::class)->finalize($invoice);

        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'document.finalized',
            'invoice_id' => $invoice->id,
        ]);

        $statement = app(StatementGenerator::class)->generate($tenant);
        $this->assertDatabaseHas('system_activity_logs', [
            'action' => 'statement.generated',
            'invoice_id' => $statement->id,
        ]);
    }
}
