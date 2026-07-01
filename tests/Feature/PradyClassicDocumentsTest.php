<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Domain\Billing\InvoiceNumberGenerator;
use App\Domain\Billing\ProformaConverter;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Billing\SampleFinancialDocumentSnapshot;
use App\Domain\Billing\StatementGenerator;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\Setting;
use App\Models\TenantInvoice;
use App\Models\User;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\DocumentNumberFormatter;
use App\Support\Billing\PradyClassicBrandAssets;
use Database\Seeders\DocumentTemplateSeeder;
use Database\Seeders\PradyClassicA5TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class PradyClassicDocumentsTest extends TestCase
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
            'issuer_tagline' => 'DOING I.T DIFFERENTLY',
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
        $this->seed(PradyClassicA5TemplateSeeder::class);
        BillingAutomationRule::platform();
    }

    private function receiptTemplate(): DocumentTemplate
    {
        return DocumentTemplate::query()
            ->where('style', 'prady_classic_a5')
            ->where('type', BillingDocumentType::RECEIPT)
            ->firstOrFail();
    }

    private function renderReceiptHtml(): string
    {
        return app(DocumentRenderer::class)->render(
            $this->receiptTemplate(),
            SampleFinancialDocumentSnapshot::receipt(),
        );
    }

    private function assertBrandHeaderPresent(string $html): void
    {
        $this->assertTrue(
            str_contains($html, 'prady-header-image') || str_contains($html, 'pc-header-fallback'),
            'Expected brand header image or CSS fallback header.',
        );
    }

    private function assertBrandFooterPresent(string $html): void
    {
        $this->assertTrue(
            str_contains($html, 'prady-footer-image') || str_contains($html, 'pc-footer-wave-fallback'),
            'Expected brand footer image or CSS/SVG fallback footer.',
        );
    }

    public function test_prady_classic_templates_exist_for_core_document_types(): void
    {
        foreach ([
            BillingDocumentType::INVOICE,
            BillingDocumentType::PROFORMA,
            BillingDocumentType::QUOTATION,
            BillingDocumentType::RECEIPT,
            BillingDocumentType::STATEMENT,
        ] as $type) {
            $template = DocumentTemplate::query()
                ->where('type', $type)
                ->where('style', 'prady_classic_a5')
                ->first();

            $this->assertNotNull($template, "Missing Prady Classic template for {$type}");
            $this->assertTrue($template->is_default);
            $this->assertSame('A5', strtoupper($template->paper_size));
            $this->assertSame('billing.documents.prady-classic-a5', $template->blade_view);
        }
    }

    public function test_short_numbering_format_for_new_documents(): void
    {
        $generator = app(InvoiceNumberGenerator::class);

        $invoiceNumber = $generator->next(BillingDocumentType::INVOICE);
        $receiptNumber = $generator->next(BillingDocumentType::RECEIPT);

        $this->assertTrue(DocumentNumberFormatter::isShortFormat($invoiceNumber));
        $this->assertTrue(DocumentNumberFormatter::isShortFormat($receiptNumber));
        $this->assertSame('No. '.$invoiceNumber, DocumentNumberFormatter::display($invoiceNumber));
    }

    public function test_existing_legacy_numbers_are_preserved(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Legacy Co');

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-2026-0099',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 100,
            'total' => 100,
            'amount_due' => 100,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('tenant_invoices', ['invoice_number' => 'INV-2026-0099']);
    }

    public function test_snapshot_includes_issuer_and_payment_options(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Snapshot Co');

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '175/24',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);

        $snapshot = app(DocumentSnapshotBuilder::class)->build($invoice->fresh(['tenant', 'lineItems', 'payments']));

        $this->assertSame('PRADY TECHNOLOGIES LIMITED', $snapshot['issuer']['legal_name']);
        $this->assertSame('P051769315N', $snapshot['issuer']['pin']);
        $this->assertSame('Absa bank', $snapshot['payment_options']['bank_name']);
        $this->assertSame('Gilgil', $snapshot['payment_options']['bank_branch']);
        $this->assertSame('No. 175/24', $snapshot['display_number']);
        $this->assertSame('No. 175/24', $snapshot['document']['display_number']);
        $this->assertSame('INVOICE', $snapshot['document']['title']);
        $this->assertSame(1000.0, $snapshot['totals']['total']);
        $this->assertSame('2040790635', $snapshot['payment_options']['account_number']);
    }

    public function test_prady_classic_html_has_no_citation_artifacts_and_uses_brand_strip(): void
    {
        $html = $this->renderReceiptHtml();

        $this->assertStringNotContainsString('[cite:', $html);
        $this->assertBrandHeaderPresent($html);
        $this->assertBrandFooterPresent($html);
        $this->assertStringContainsString('pc-footer-wave', $html);
        $this->assertStringContainsString('Nakuru, Barnabas, Opp. Epic Academy', $html);
        $this->assertStringContainsString('Thank you.', $html);
        $this->assertStringContainsString('39/26', $html);
        $this->assertStringContainsString('PAGE CAPITAL LTD', $html);
        $this->assertStringContainsString('30,000/=', $html);
        $this->assertStringNotContainsString('background-image', $html);
    }

    public function test_receipt_renders_header_image_when_asset_exists(): void
    {
        if (! PradyClassicBrandAssets::headerExists()) {
            $this->markTestSkipped('Header brand asset not present.');
        }

        $html = $this->renderReceiptHtml();

        $this->assertStringContainsString('prady-header-image', $html);
        $this->assertStringNotContainsString('pc-header-fallback', $html);
        $this->assertMatchesRegularExpression('/<img[^>]+class="prady-header-image"/', $html);
    }

    public function test_receipt_renders_footer_image_when_asset_exists(): void
    {
        if (! PradyClassicBrandAssets::footerExists()) {
            $this->markTestSkipped('Footer brand asset not present.');
        }

        $html = $this->renderReceiptHtml();

        $this->assertStringContainsString('prady-footer-image', $html);
        $this->assertStringNotContainsString('pc-footer-wave-fallback', $html);
        $this->assertMatchesRegularExpression('/<img[^>]+class="prady-footer-image/', $html);
    }

    public function test_receipt_renders_css_header_fallback_when_image_missing(): void
    {
        config(['billing.document_brand_header_path' => storage_path('app/nonexistent-prady-header.png')]);

        $html = $this->renderReceiptHtml();

        $this->assertStringContainsString('pc-header-fallback', $html);
        $this->assertStringContainsString('PRADY', $html);
        $this->assertStringContainsString('DOING I.T DIFFERENTLY', $html);
        $this->assertDoesNotMatchRegularExpression('/<img[^>]+class="prady-header-image"/', $html);
    }

    public function test_receipt_renders_css_footer_fallback_when_image_missing(): void
    {
        config(['billing.document_brand_footer_path' => storage_path('app/nonexistent-prady-footer.png')]);

        $html = $this->renderReceiptHtml();

        $this->assertStringContainsString('pc-footer-wave-fallback', $html);
        $this->assertDoesNotMatchRegularExpression('/<img[^>]+class="prady-footer-image/', $html);
    }

    public function test_all_core_document_types_render_with_brand_assets_or_fallback(): void
    {
        $renderer = app(DocumentRenderer::class);
        $samples = [
            BillingDocumentType::INVOICE => SampleFinancialDocumentSnapshot::invoice(),
            BillingDocumentType::PROFORMA => SampleFinancialDocumentSnapshot::proforma(),
            BillingDocumentType::QUOTATION => SampleFinancialDocumentSnapshot::quotation(),
            BillingDocumentType::RECEIPT => SampleFinancialDocumentSnapshot::receipt(),
            BillingDocumentType::STATEMENT => SampleFinancialDocumentSnapshot::statement(),
        ];

        foreach ($samples as $type => $snapshot) {
            $template = DocumentTemplate::query()
                ->where('style', 'prady_classic_a5')
                ->where('type', $type)
                ->firstOrFail();

            $html = $renderer->render($template, $snapshot);

            $this->assertBrandHeaderPresent($html);
            $this->assertBrandFooterPresent($html);
            $this->assertStringContainsString('Thank you.', $html);
        }
    }

    public function test_pdf_generation_still_works_with_brand_assets(): void
    {
        $pdf = app(\App\Domain\Billing\PdfGenerator::class);
        if (! $pdf->isAvailable()) {
            $this->markTestSkipped('dompdf not installed');
        }

        $html = $this->renderReceiptHtml();
        $path = $pdf->store($html, 'test/prady-classic-receipt-brand.pdf', 'A5', 'portrait');

        $this->assertNotNull($path);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($path));
        $this->assertGreaterThan(1000, \Illuminate\Support\Facades\Storage::disk('local')->size($path));
    }

    public function test_receipt_snapshot_includes_paid_and_balance(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Receipt Co');

        $receipt = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '39/26',
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => 'KES',
            'subtotal' => 500,
            'total' => 500,
            'amount_due' => 0,
            'amount_paid' => 500,
            'status' => 'paid',
            'issue_date' => now()->toDateString(),
        ]);

        $snapshot = app(DocumentSnapshotBuilder::class)->build($receipt->fresh(['tenant', 'lineItems', 'payments']));

        $this->assertSame(500.0, $snapshot['amount_paid']);
        $this->assertSame(0.0, $snapshot['balance_due']);
        $this->assertSame(500.0, $snapshot['totals']['paid']);
        $this->assertSame(0.0, $snapshot['totals']['balance']);
    }

    public function test_proforma_conversion_uses_default_invoice_template(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Convert Co');

        $proforma = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '10/26',
            'document_type' => BillingDocumentType::PROFORMA,
            'currency' => 'KES',
            'subtotal' => 2000,
            'tax_amount' => 0,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);

        $invoice = app(ProformaConverter::class)->convert($proforma);

        $defaultInvoiceTemplate = DocumentTemplate::query()
            ->where('type', BillingDocumentType::INVOICE)
            ->where('style', 'prady_classic_a5')
            ->where('is_default', true)
            ->first();

        $this->assertSame($defaultInvoiceTemplate?->id, $invoice->document_template_id);
    }

    public function test_statement_generation_builds_real_rows(): void
    {
        $user = User::factory()->create();
        [, , $tenant] = $this->createTenantWithSubscription('Statement Co');

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '5/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 3000,
            'total' => 3000,
            'amount_due' => 3000,
            'status' => 'sent',
            'issue_date' => now()->startOfMonth()->toDateString(),
        ]);

        $statement = app(StatementGenerator::class)->generate($tenant);

        $this->assertSame(BillingDocumentType::STATEMENT, $statement->document_type);
        $document = $statement->generatedDocuments()->latest('id')->first();
        $this->assertNotNull($document);
        $snapshot = $document->data_snapshot;

        $this->assertNotEmpty($snapshot['statement_rows'] ?? []);
        $this->assertNotEmpty($snapshot['statement']['rows'] ?? []);
        $this->assertArrayHasKey('closing_balance', $snapshot);
        $this->assertArrayHasKey('period_start', $snapshot['statement']);
    }
}
