<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\PdfGenerator;
use App\Domain\Billing\SampleFinancialDocumentSnapshot;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\Setting;
use App\Support\Billing\BillingDocumentType;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PradyClassicVisualDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', [
            'company_legal_name' => 'PRADY TECHNOLOGIES LIMITED',
            'trading_name' => 'Prady Technologies',
            'tax_pin' => 'P051769315N',
            'issuer_phone' => '0110 098 548 / 0722 295 194',
            'issuer_email' => 'info@pradytec.com',
            'issuer_website' => 'www.pradytec.com',
            'issuer_address' => 'Nakuru, Barnabas, Opp. Epic Academy',
            'issuer_tagline' => 'DOING I.T DIFFERENTLY',
            'bank_name' => 'Absa bank',
            'bank_account_name' => 'P technologies Ltd',
            'bank_account_number' => '2040790635',
            'bank_branch' => 'Gilgil',
            'mpesa_paybill' => '918885',
            'paybill_account_number' => 'PAGECAPITAL',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform()->update(['auto_generate_pdf' => true]);
    }

    public function test_invoice_html_matches_print_form_structure(): void
    {
        $this->assertPrintFormStructure(SampleFinancialDocumentSnapshot::invoice());
    }

    public function test_receipt_html_matches_print_form_structure(): void
    {
        $this->assertPrintFormStructure(SampleFinancialDocumentSnapshot::receipt());
    }

    public function test_proforma_html_matches_print_form_structure(): void
    {
        $this->assertPrintFormStructure(SampleFinancialDocumentSnapshot::proforma());
    }

    public function test_quotation_html_matches_print_form_structure(): void
    {
        $this->assertPrintFormStructure(SampleFinancialDocumentSnapshot::quotation());
    }

    public function test_statement_html_matches_print_form_structure(): void
    {
        $type = 'statement';
        $template = DocumentTemplate::query()
            ->where('style', 'prady_classic_a5')
            ->where('type', $type)
            ->firstOrFail();

        $html = app(DocumentRenderer::class)->render($template, SampleFinancialDocumentSnapshot::statement());

        $this->assertBrandHeaderPresent($html);
        $this->assertStringContainsString('pc-footer-wave', $html);
        $this->assertStringContainsString('ACCOUNT STATEMENT', $html);
    }

    public function test_manual_preview_endpoint_renders_prady_classic_html(): void
    {
        $this->seed(\Database\Seeders\PradyClassicA5TemplateSeeder::class);

        $template = DocumentTemplate::query()
            ->where('style', 'prady_classic_a5')
            ->where('type', 'invoice')
            ->firstOrFail();

        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('invoices.manual.preview'), [
            'document_type' => 'invoice',
            'issue_date' => '2026-03-03',
            'currency' => 'KES',
            'manual_client_name' => 'PAGE CAPITAL LTD',
            'line_items' => [[
                'description' => 'Prady Technologies Microfinance Renting',
                'quantity' => 1,
                'unit_price' => 30000,
                'discount' => 0,
                'tax_rate' => 0,
                'item_type' => 'custom',
            ]],
        ]);

        $response->assertOk();
        $html = (string) $response->json('html');
        $this->assertBrandHeaderPresent($html);
        $this->assertStringContainsString('pc-ledger', $html);
        $this->assertSame('A5', $response->json('paper_size'));
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

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function assertPrintFormStructure(array $snapshot): void
    {
        $type = $snapshot['document_type'];
        $template = DocumentTemplate::query()
            ->where('style', 'prady_classic_a5')
            ->where('type', $type)
            ->firstOrFail();

        $html = app(DocumentRenderer::class)->render($template, $snapshot);

        $this->assertBrandHeaderPresent($html);
        $this->assertStringContainsString('pc-ledger', $html);
        $this->assertBrandFooterPresent($html);
        $this->assertStringContainsString('Item Description', $html);
        $this->assertStringNotContainsString('[cite:', $html);
    }

    public function test_receipt_pdf_renders_on_a5(): void
    {
        $pdf = app(PdfGenerator::class);
        if (! $pdf->isAvailable()) {
            $this->markTestSkipped('dompdf not installed');
        }

        $template = DocumentTemplate::query()
            ->where('style', 'prady_classic_a5')
            ->where('type', BillingDocumentType::RECEIPT)
            ->firstOrFail();

        $html = app(DocumentRenderer::class)->render($template, SampleFinancialDocumentSnapshot::receipt());
        $path = $pdf->store($html, 'test/prady-receipt-'.uniqid('', true).'.pdf', 'A5', 'portrait');

        $this->assertNotNull($path);
        $this->assertTrue(Storage::disk('local')->exists($path));
        $this->assertGreaterThan(800, Storage::disk('local')->size($path));
    }
}
