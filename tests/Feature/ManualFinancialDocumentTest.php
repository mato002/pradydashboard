<?php

namespace Tests\Feature;

use App\Domain\Billing\ManualDocumentCreator;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\User;
use App\Support\Billing\BillingDocumentType;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualFinancialDocumentTest extends TestCase
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
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
    }

    private function userAndTenant(): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Manual Co', 'domain' => 'manual.test', 'currency' => 'KES']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Manual Tenant Ltd',
            'billing_email' => 'bill@manual.test',
            'billing_contact_name' => 'Jane Doe',
            'tenant_currency' => 'KES',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        return [$user, $tenant];
    }

    /** @return array<string, mixed> */
    private function linePayload(): array
    {
        return [
            'line_items' => [
                [
                    'description' => 'Consulting hours',
                    'quantity' => 2,
                    'unit_price' => 5000,
                    'discount' => 0,
                    'tax_rate' => 16,
                    'item_type' => 'custom',
                ],
            ],
        ];
    }

    public function test_manual_invoice_with_tenant(): void
    {
        [$user, $tenant] = $this->userAndTenant();

        $response = $this->actingAs($user)
            ->post(route('invoices.manual.store'), array_merge([
                'document_type' => BillingDocumentType::INVOICE,
                'tenant_id' => $tenant->id,
                'issue_date' => now()->toDateString(),
                'currency' => 'KES',
            ], $this->linePayload()));

        $invoice = TenantInvoice::query()->where('tenant_id', $tenant->id)->latest('id')->first();
        $response->assertRedirect(route('invoices.preview', $invoice));
        $this->assertNotNull($invoice);
        $this->assertSame('invoice', $invoice->document_type);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame('manual', $invoice->created_source);
        $this->assertSame(10000.0, (float) $invoice->subtotal);
        $this->assertSame(1600.0, (float) $invoice->tax_amount);
        $this->assertSame(11600.0, (float) $invoice->total);
    }

    public function test_manual_invoice_without_tenant_requires_client_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('invoices.manual.store'), array_merge([
                'document_type' => BillingDocumentType::INVOICE,
                'tenant_id' => '',
                'issue_date' => now()->toDateString(),
                'currency' => 'KES',
                'manual_client_name' => 'Walk-in Client',
                'manual_client_email' => 'walk@example.com',
            ], $this->linePayload()))
            ->assertRedirect();

        $invoice = TenantInvoice::query()->whereNull('tenant_id')->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertSame('Walk-in Client', $invoice->manual_client_name);
    }

    public function test_line_totals_calculated_server_side(): void
    {
        $calc = app(\App\Domain\Billing\ManualLineItemCalculator::class);
        $result = $calc->compute([
            ['description' => 'A', 'quantity' => 2, 'unit_price' => 100, 'discount' => 10, 'tax_rate' => 10],
        ]);

        $this->assertSame(190.0, $result['subtotal']);
        $this->assertSame(19.0, $result['tax_amount']);
        $this->assertSame(209.0, $result['total']);
    }

    public function test_amount_paid_stored_on_manual_invoice(): void
    {
        [, $tenant] = $this->userAndTenant();
        $invoice = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::INVOICE,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
            'amount_paid' => 5000,
        ], $this->linePayload()));

        $this->assertSame(5000.0, (float) $invoice->amount_paid);
        $this->assertGreaterThan(0, $invoice->balanceDue());
    }

    public function test_manual_proforma_creation(): void
    {
        [$user, $tenant] = $this->userAndTenant();

        $this->actingAs($user)
            ->post(route('invoices.manual.store'), array_merge([
                'document_type' => BillingDocumentType::PROFORMA,
                'tenant_id' => $tenant->id,
                'issue_date' => now()->toDateString(),
                'currency' => 'KES',
            ], $this->linePayload()))
            ->assertRedirect();

        $proforma = TenantInvoice::query()->where('document_type', 'proforma')->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($proforma);
        $this->assertSame('draft', $proforma->status);
    }

    public function test_manual_quotation_stays_draft_with_amount_paid(): void
    {
        [, $tenant] = $this->userAndTenant();

        $quotation = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::QUOTATION,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
            'amount_paid' => 1000,
        ], $this->linePayload()));

        $this->assertSame('draft', $quotation->status);
        $this->assertSame('pending', $quotation->approval_status);
    }

    public function test_partial_linked_receipt_generates_document(): void
    {
        [, $tenant] = $this->userAndTenant();
        $invoice = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::INVOICE,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ], $this->linePayload()));

        app(ManualDocumentCreator::class)->create([
            'document_type' => BillingDocumentType::RECEIPT,
            'linked_invoice_id' => $invoice->id,
            'amount_received' => 1000,
            'payment_method' => 'mpesa',
            'payment_date' => now()->toDateString(),
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ]);

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertTrue(
            TenantInvoice::query()->where('document_type', 'receipt')->where('linked_invoice_id', $invoice->id)->exists()
        );
    }

    public function test_manual_quotation_conversion(): void
    {
        [, $tenant] = $this->userAndTenant();
        $quotation = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::QUOTATION,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ], $this->linePayload()));

        app(QuotationConverter::class)->approve($quotation);
        $invoice = app(QuotationConverter::class)->convert($quotation->fresh(['lineItems']));

        $this->assertSame('invoice', $invoice->document_type);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'quotation.converted')->exists());
    }

    public function test_linked_receipt_records_payment(): void
    {
        [, $tenant] = $this->userAndTenant();
        $invoice = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::INVOICE,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ], $this->linePayload()));

        app(ManualDocumentCreator::class)->create([
            'document_type' => BillingDocumentType::RECEIPT,
            'linked_invoice_id' => $invoice->id,
            'amount_received' => $invoice->total,
            'payment_method' => 'mpesa',
            'payment_date' => now()->toDateString(),
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ]);

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertTrue(
            TenantInvoice::query()->where('document_type', 'receipt')->where('linked_invoice_id', $invoice->id)->exists()
        );
    }

    public function test_standalone_receipt_without_tenant(): void
    {
        $receipt = app(ManualDocumentCreator::class)->create([
            'document_type' => BillingDocumentType::RECEIPT,
            'manual_client_name' => 'Cash Customer',
            'amount_received' => 1500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
            'line_description' => 'On-site payment',
        ]);

        $this->assertSame('receipt', $receipt->document_type);
        $this->assertNull($receipt->tenant_id);
        $this->assertSame('Cash Customer', $receipt->manual_client_name);
        $this->assertSame(1500.0, (float) $receipt->total);
    }

    public function test_create_form_loads(): void
    {
        [$user] = $this->userAndTenant();

        $this->actingAs($user)
            ->get(route('invoices.create', ['type' => 'invoice']))
            ->assertOk()
            ->assertSee(__('Save as draft'))
            ->assertSee(__('Document template'))
            ->assertSee(__('No tenant selected'))
            ->assertSee(__('Live total preview'))
            ->assertSee('id="manual-document-create-layout"', false)
            ->assertSee('manual-document-create-layout', false)
            ->assertSee('manual-document-create-preview', false)
            ->assertSee('id="manual-document-preview"', false)
            ->assertSee(__('Preview only — final totals calculated on save'))
            ->assertSee('preview-a5', false)
            ->assertSee('INVOICE', false);
    }

    public function test_create_proforma_shows_prady_classic_template_and_preview(): void
    {
        [$user] = $this->userAndTenant();

        $this->actingAs($user)
            ->get(route('invoices.create', ['type' => 'proforma']))
            ->assertOk()
            ->assertSee('Prady Classic A5 Proforma', false)
            ->assertSee('data-preview-layout="prady_classic_a5"', false)
            ->assertSee('PROFORMA INVOICE', false);
    }

    public function test_create_quotation_preview_title(): void
    {
        [$user] = $this->userAndTenant();

        $this->actingAs($user)
            ->get(route('invoices.create', ['type' => 'quotation']))
            ->assertOk()
            ->assertSee('QUOTATION', false);
    }

    public function test_create_receipt_preview_title(): void
    {
        [$user] = $this->userAndTenant();

        $this->actingAs($user)
            ->get(route('invoices.create', ['type' => 'receipt']))
            ->assertOk()
            ->assertSee('RECEIPT', false)
            ->assertSee(__('View Preview'), false);
    }

    public function test_document_preview_shows_paid_and_balance_when_amount_paid(): void
    {
        [, $tenant] = $this->userAndTenant();
        $invoice = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::INVOICE,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
            'amount_paid' => 10000,
        ], $this->linePayload()));

        $this->assertSame(11600.0, (float) $invoice->total);
        $this->assertSame(10000.0, (float) $invoice->amount_paid);
        $this->assertSame(1600.0, $invoice->balanceDue());

        $html = app(\App\Domain\Billing\DocumentRenderer::class)->render(
            \App\Models\DocumentTemplate::query()->where('style', 'modern_saas')->where('type', 'invoice')->firstOrFail(),
            app(\App\Domain\Billing\DocumentSnapshotBuilder::class)->build($invoice),
        );

        $this->assertStringContainsString(__('Paid'), $html);
        $this->assertStringContainsString('10,000.00', $html);
        $this->assertStringContainsString(__('Balance'), $html);
        $this->assertStringContainsString('1,600.00', $html);
    }

    public function test_invoice_preview_iframe_uses_unescaped_srcdoc(): void
    {
        [$user, $tenant] = $this->userAndTenant();
        $invoice = app(ManualDocumentCreator::class)->create(array_merge([
            'document_type' => BillingDocumentType::INVOICE,
            'tenant_id' => $tenant->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'KES',
        ], $this->linePayload()));

        $this->actingAs($user)
            ->get(route('invoices.preview', $invoice))
            ->assertOk()
            ->assertSee('invoice-document-split-layout', false)
            ->assertSee('invoice-document-split-preview', false)
            ->assertSee('sandbox="allow-same-origin"', false)
            ->assertDontSee('srcdoc="&lt;div', false);
    }

    public function test_manual_store_uses_server_totals_not_browser_only(): void
    {
        [$user, $tenant] = $this->userAndTenant();

        $this->actingAs($user)
            ->post(route('invoices.manual.store'), array_merge([
                'document_type' => BillingDocumentType::INVOICE,
                'tenant_id' => $tenant->id,
                'issue_date' => now()->toDateString(),
                'currency' => 'KES',
            ], $this->linePayload()))
            ->assertRedirect();

        $invoice = TenantInvoice::query()->where('tenant_id', $tenant->id)->latest('id')->first();
        $this->assertSame(10000.0, (float) $invoice->subtotal);
        $this->assertSame(1600.0, (float) $invoice->tax_amount);
        $this->assertSame(11600.0, (float) $invoice->total);
    }
}
