<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\InvoiceEmailDelivery;
use App\Domain\Billing\ManualDocumentPreviewBuilder;
use App\Domain\Billing\SampleFinancialDocumentSnapshot;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Setting;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\User;
use App\Support\Billing\BillingDocumentType;
use Database\Seeders\DocumentTemplateSeeder;
use Database\Seeders\PradyClassicA5TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class PradyClassicProductionCertificationTest extends TestCase
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
            'issuer_phone' => '0110 098 548 / 0722 295 194',
            'issuer_email' => 'info@pradytec.com',
            'issuer_website' => 'www.pradytec.com',
            'issuer_address' => 'Nakuru, Barnabas, Opp. Epic Academy',
            'issuer_tagline' => 'DOING I.T DIFFERENTLY',
            'numbering_style' => 'short',
            'bank_name' => 'Absa bank',
            'bank_account_number' => '2040790635',
            'mpesa_paybill' => '918885',
            'paybill_account_number' => 'PAGECAPITAL',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        $this->seed(PradyClassicA5TemplateSeeder::class);
        BillingAutomationRule::platform()->update(['auto_generate_pdf' => true]);

        config([
            'billing.issuer_phone' => '0110 098 548 / 0722 295 194',
            'billing.issuer_email' => 'info@pradytec.com',
            'billing.issuer_website' => 'www.pradytec.com',
            'billing.issuer_address' => 'Nakuru, Barnabas, Opp. Epic Academy',
            'billing.issuer_tagline' => 'DOING I.T DIFFERENTLY',
        ]);
    }

    /**
     * @return array<string, DocumentTemplate>
     */
    private function pradyTemplates(): array
    {
        $types = [
            BillingDocumentType::INVOICE,
            BillingDocumentType::PROFORMA,
            BillingDocumentType::QUOTATION,
            BillingDocumentType::RECEIPT,
            BillingDocumentType::STATEMENT,
        ];

        $templates = [];
        foreach ($types as $type) {
            $templates[$type] = DocumentTemplate::query()
                ->where('style', 'prady_classic_a5')
                ->where('type', $type)
                ->firstOrFail();
        }

        return $templates;
    }

    private function assertBrandHeaderPresent(string $html, string $message = ''): void
    {
        $this->assertTrue(
            str_contains($html, 'prady-header-image') || str_contains($html, 'pc-header-fallback'),
            $message !== '' ? $message : 'Expected brand header image or CSS fallback header.',
        );
    }

    private function assertBrandFooterPresent(string $html, string $message = ''): void
    {
        $this->assertTrue(
            str_contains($html, 'prady-footer-image') || str_contains($html, 'pc-footer-wave-fallback'),
            $message !== '' ? $message : 'Expected brand footer image or CSS/SVG fallback footer.',
        );
    }

    public function test_all_prady_classic_types_contain_reference_visual_markers(): void
    {
        $renderer = app(DocumentRenderer::class);
        $samples = [
            BillingDocumentType::INVOICE => SampleFinancialDocumentSnapshot::invoice(),
            BillingDocumentType::PROFORMA => SampleFinancialDocumentSnapshot::proforma(),
            BillingDocumentType::QUOTATION => SampleFinancialDocumentSnapshot::quotation(),
            BillingDocumentType::RECEIPT => SampleFinancialDocumentSnapshot::receipt(),
            BillingDocumentType::STATEMENT => SampleFinancialDocumentSnapshot::statement(),
        ];

        foreach ($this->pradyTemplates() as $type => $template) {
            $html = $renderer->render($template, $samples[$type]);

            $this->assertBrandHeaderPresent($html, "Missing header for {$type}");
            if ($type === BillingDocumentType::STATEMENT) {
                $this->assertStringContainsString('pc-stmt-ledger', $html, "Missing statement ledger for {$type}");
            } else {
                $this->assertStringContainsString('pc-ledger', $html, "Missing ledger for {$type}");
            }
            $this->assertBrandFooterPresent($html, "Missing footer for {$type}");
            $this->assertStringContainsString('Nakuru, Barnabas, Opp. Epic Academy', $html);
            $this->assertStringContainsString('info@pradytec.com', $html);
            $this->assertStringContainsString('Thank you.', $html);
            $this->assertStringNotContainsString('Thank you for choosing', $html);
            $this->assertStringNotContainsString('Nairobi, Kenya', $html);
            $this->assertStringNotContainsString('billing@pradytech.test', $html);
            $this->assertStringNotContainsString('[cite:', $html);
            $this->assertStringNotContainsString('INV-2026', $html);
        }
    }

    public function test_receipt_html_shows_paid_and_balance_boxes(): void
    {
        $template = $this->pradyTemplates()[BillingDocumentType::RECEIPT];
        $html = app(DocumentRenderer::class)->render($template, SampleFinancialDocumentSnapshot::receipt());

        $this->assertStringContainsString('PAID', $html);
        $this->assertStringContainsString('BALANCE', $html);
        $this->assertStringContainsString('30,000/=', $html);
    }

    public function test_short_number_display_format_in_rendered_html(): void
    {
        $template = $this->pradyTemplates()[BillingDocumentType::RECEIPT];
        $html = app(DocumentRenderer::class)->render($template, SampleFinancialDocumentSnapshot::receipt());

        $this->assertStringContainsString('39/26', $html);
        $this->assertStringContainsString('pc-meta-no-val', $html);
    }

    public function test_finalized_snapshot_contains_required_safe_keys(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Snapshot Co');
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '15/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 5000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Certification line',
            'quantity' => 1,
            'unit_price' => 5000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 5000,
        ]);

        $document = app(DocumentFinalizer::class)->finalize($invoice->fresh(['lineItems', 'tenant']));
        $snapshot = $document->data_snapshot;

        foreach (['issuer', 'document', 'client', 'line_items', 'totals', 'payment_options', 'delivery', 'conversion_links'] as $key) {
            $this->assertArrayHasKey($key, $snapshot, "Missing snapshot key: {$key}");
        }

        $this->assertArrayHasKey('pin', $snapshot['issuer']);
        $encoded = json_encode($snapshot);
        $this->assertStringNotContainsString('password', strtolower($encoded));
        $this->assertStringNotContainsString('secret', strtolower($encoded));
        $this->assertStringNotContainsString('storage/app', $encoded);
    }

    public function test_finalized_preview_uses_persisted_html_snapshot(): void
    {
        [, $tenant] = $this->createTenantWithSubscription('Preview Co');
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '16/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Frozen line',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
        ]);

        $document = app(DocumentFinalizer::class)->finalize($invoice->fresh(['lineItems', 'tenant']));
        $marker = '<!--cert-marker-'.uniqid('', true).'-->';
        $document->update(['html_snapshot' => $document->html_snapshot.$marker]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('invoices.preview', $invoice));

        $response->assertOk();
        $response->assertSee($marker, false);
    }

    public function test_email_delivery_uses_generated_pdf_attachment(): void
    {
        Mail::fake();
        Storage::fake('local');

        [, $tenant] = $this->createTenantWithSubscription('Email Co', [
            'billing_email' => 'billing@email-co.test',
        ]);
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => '17/26',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'subtotal' => 2000,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Email line',
            'quantity' => 1,
            'unit_price' => 2000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 2000,
        ]);

        $document = app(DocumentFinalizer::class)->finalize($invoice->fresh(['lineItems', 'tenant']));
        Storage::disk('local')->put($document->pdf_path, '%PDF-cert-test');

        $result = app(InvoiceEmailDelivery::class)->send(
            $invoice->fresh(),
            $document->fresh(),
            'billing@email-co.test',
            false,
        );

        $this->assertTrue($result['success']);
        Mail::assertSent(\App\Mail\FinancialDocumentMail::class, function ($mail) use ($document) {
            return $mail->pdfPath === $document->pdf_path;
        });
    }

    public function test_manual_preview_uses_draft_number_not_legacy_prefix(): void
    {
        $template = $this->pradyTemplates()[BillingDocumentType::INVOICE];
        $snapshot = app(ManualDocumentPreviewBuilder::class)->build([
            'document_type' => BillingDocumentType::INVOICE,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
            'manual_client_name' => 'Cert Client',
            'line_items' => [[
                'description' => 'Preview line',
                'quantity' => 1,
                'unit_price' => 100,
                'discount' => 0,
                'tax_rate' => 0,
                'item_type' => 'custom',
            ]],
        ]);

        $this->assertSame('DRAFT', $snapshot['invoice_number']);
        $this->assertSame('No. DRAFT', $snapshot['display_number']);

        $html = app(DocumentRenderer::class)->render($template, $snapshot);
        $this->assertStringContainsString('DRAFT', $html);
        $this->assertStringNotContainsString('INV-2026', $html);
        $this->assertStringNotContainsString('INV-DRAFT', $html);
    }
}
