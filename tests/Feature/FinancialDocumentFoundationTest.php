<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentIdentityService;
use App\Domain\Billing\DocumentPresentationResolver;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Domain\Billing\InvoiceNumberGenerator;
use App\Domain\Billing\StatementGenerator;
use App\Models\BillingAutomationRule;
use App\Models\Setting;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\DocumentNumberFormatter;
use App\Support\Billing\FinancialDocumentRegistry;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class FinancialDocumentFoundationTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', [
            'company_legal_name' => 'PRADY TECHNOLOGIES LIMITED',
            'trading_name' => 'Prady Technologies',
            'tax_pin' => 'P051769315N',
            'numbering_style' => 'short',
            'number_sequence_padding' => '0',
            'bank_name' => 'Example Bank',
            'bank_account_name' => 'PRADY TECHNOLOGIES LIMITED',
            'bank_account_number' => '1234567890',
            'bank_branch' => 'Westlands',
            'mpesa_paybill' => '522522',
            'paybill_account_number' => 'PRADY',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
    }

    public function test_identity_service_generates_short_format(): void
    {
        $identity = app(DocumentIdentityService::class);

        $number = $identity->generate(BillingDocumentType::INVOICE);

        $this->assertTrue($identity->isShortFormat($number));
        $this->assertSame('No. '.$number, $identity->formatDisplayNumber($number));
    }

    public function test_invoice_number_generator_delegates_to_identity_service(): void
    {
        $number = app(InvoiceNumberGenerator::class)->next(BillingDocumentType::QUOTATION);

        $this->assertTrue(DocumentNumberFormatter::isShortFormat($number));
    }

    public function test_separate_document_types_can_share_same_short_number(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Dup Co');

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '1/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 100,
            'total' => 100,
            'amount_due' => 100,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '1/26',
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => 'KES',
            'subtotal' => 100,
            'total' => 100,
            'amount_due' => 0,
            'amount_paid' => 100,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseCount('tenant_invoices', 2);
    }

    public function test_registry_returns_titles_for_core_types(): void
    {
        $this->assertSame('INVOICE', FinancialDocumentRegistry::title(BillingDocumentType::INVOICE));
        $this->assertSame('PROFORMA INVOICE', FinancialDocumentRegistry::title(BillingDocumentType::PROFORMA));
        $this->assertSame('QUOTATION', FinancialDocumentRegistry::title(BillingDocumentType::QUOTATION));
        $this->assertSame('RECEIPT', FinancialDocumentRegistry::title(BillingDocumentType::RECEIPT));
        $this->assertSame('ACCOUNT STATEMENT', FinancialDocumentRegistry::title(BillingDocumentType::STATEMENT));
    }

    public function test_registry_section_flags(): void
    {
        $this->assertTrue(FinancialDocumentRegistry::showsPaidBalance(BillingDocumentType::RECEIPT));
        $this->assertFalse(FinancialDocumentRegistry::showsPaidBalance(BillingDocumentType::QUOTATION));
        $this->assertTrue(FinancialDocumentRegistry::showsStatementRows(BillingDocumentType::STATEMENT));
        $this->assertTrue(FinancialDocumentRegistry::supportsConversion(BillingDocumentType::PROFORMA));
    }

    public function test_snapshot_contract_contains_structured_sections(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Contract Co');

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '175/24',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 1000,
            'tax_amount' => 0,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);

        $snapshot = app(DocumentSnapshotBuilder::class)->build($invoice->fresh(['tenant', 'lineItems', 'payments']));

        $this->assertSame('PRADY TECHNOLOGIES LIMITED', $snapshot['issuer']['legal_name']);
        $this->assertSame('P051769315N', $snapshot['issuer']['pin']);
        $this->assertSame('No. 175/24', $snapshot['document']['display_number']);
        $this->assertSame('INVOICE', $snapshot['document']['title']);
        $this->assertSame('Absa bank', $snapshot['payment_options']['bank_name']);
        $this->assertSame('2040790635', $snapshot['payment_options']['account_number']);
        $this->assertSame('CONTRACTCO', $snapshot['payment_options']['paybill_account']);
        $this->assertSame('CONTRACTCO', $snapshot['payment_options']['paybill_account_number']);
        $this->assertNotSame('PRADY', $snapshot['payment_options']['paybill_account']);
        $this->assertSame(1000.0, $snapshot['totals']['total']);
        $this->assertArrayHasKey('presentation', $snapshot);
        $this->assertTrue($snapshot['presentation']['enabled_sections']['payment_options']);
    }

    public function test_snapshot_paybill_account_uses_manual_client_name(): void
    {
        $invoice = TenantInvoice::query()->create([
            'invoice_number' => '99/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'manual_client_name' => 'Acme Holdings Ltd',
        ]);

        $snapshot = app(DocumentSnapshotBuilder::class)->build($invoice->fresh(['tenant', 'lineItems', 'payments']));

        $this->assertSame('ACMEHOLDINGS', $snapshot['payment_options']['paybill_account']);
    }

    public function test_presentation_resolver_returns_metadata_without_extra_queries_in_resolve(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Present Co');

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '7/26',
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => 'KES',
            'subtotal' => 500,
            'total' => 500,
            'amount_due' => 0,
            'amount_paid' => 500,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
        ]);

        $meta = app(DocumentPresentationResolver::class)->resolve($invoice);

        $this->assertSame('RECEIPT', $meta['document_title']);
        $this->assertSame('No. 7/26', $meta['display_number']);
        $this->assertSame('A5', $meta['paper_size']);
        $this->assertTrue($meta['enabled_sections']['paid_balance']);
    }

    public function test_statement_snapshot_contains_period_and_rows(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Stmt Co');

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '3/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 2000,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'sent',
            'issue_date' => now()->startOfMonth()->toDateString(),
        ]);

        $statement = app(StatementGenerator::class)->generate($tenant);
        $snapshot = $statement->generatedDocuments()->latest('id')->first()?->data_snapshot ?? [];

        $this->assertNotEmpty($snapshot['statement']['rows'] ?? []);
        $this->assertNotNull($snapshot['statement']['period_start'] ?? null);
        $this->assertArrayHasKey('closing_balance', $snapshot['statement']);
        $this->assertSame($snapshot['statement']['closing_balance'], $snapshot['closing_balance']);
    }
}
