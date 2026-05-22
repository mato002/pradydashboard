<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentDeliveryService;
use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\PdfGenerator;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Mail\FinancialDocumentMail;
use App\Models\BillingAutomationRule;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\User;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDeliveryTest extends TestCase
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
            'company_legal_name' => 'Prady Tech Ltd',
            'payment_instructions' => 'Pay via bank transfer.',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
        Storage::fake('local');
    }

    private function userWithTenant(?string $billingEmail = 'billing@tenant.test'): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Delivery Co', 'domain' => 'delivery.test', 'currency' => 'KES']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Delivery Tenant',
            'billing_email' => $billingEmail,
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        return [$user, $tenant];
    }

    private function invoiceFor(Tenant $tenant, array $overrides = []): TenantInvoice
    {
        $invoice = TenantInvoice::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-DEL-001',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 1000,
            'tax_amount' => 0,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
            'delivery_status' => 'not_sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ], $overrides));

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service fee',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
        ]);

        return $invoice->fresh(['lineItems', 'tenant']);
    }

    private function stubPdf(): void
    {
        $mock = \Mockery::mock(PdfGenerator::class);
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('store')->andReturnUsing(function (string $html, string $path): string {
            Storage::disk('local')->put($path, '%PDF-fake');

            return $path;
        });
        $this->app->instance(PdfGenerator::class, $mock);
    }

    public function test_email_sends_to_tenant_billing_email(): void
    {
        Mail::fake();
        $this->stubPdf();

        [, $tenant] = $this->userWithTenant('billing@tenant.test');
        $invoice = $this->invoiceFor($tenant);

        $result = app(DocumentDeliveryService::class)->sendEmail($invoice);

        $this->assertTrue($result['success']);
        Mail::assertSent(FinancialDocumentMail::class, function (FinancialDocumentMail $mail): bool {
            return $mail->hasTo('billing@tenant.test');
        });

        $invoice->refresh();
        $this->assertSame('sent', $invoice->delivery_status);
        $this->assertNotNull($invoice->email_sent_at);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'document.emailed')->exists());
    }

    public function test_email_sends_to_manual_client_email(): void
    {
        Mail::fake();
        $this->stubPdf();

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => null,
            'manual_client_name' => 'Walk-in',
            'manual_client_email' => 'walkin@example.com',
            'invoice_number' => 'INV-WALK-01',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 500,
            'total' => 500,
            'amount_due' => 500,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
        ]);
        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 500,
            'line_total' => 500,
        ]);

        $result = app(DocumentDeliveryService::class)->sendEmail($invoice->fresh(['lineItems']));

        $this->assertTrue($result['success']);
        Mail::assertSent(FinancialDocumentMail::class, fn ($m) => $m->hasTo('walkin@example.com'));
    }

    public function test_pdf_download_works_via_controlled_route(): void
    {
        $this->stubPdf();
        [$user, $tenant] = $this->userWithTenant();
        $invoice = $this->invoiceFor($tenant);

        app(DocumentFinalizer::class)->finalize($invoice);
        app(DocumentDeliveryService::class)->ensurePdf($invoice->fresh());

        $this->actingAs($user)
            ->get(route('invoices.pdf', $invoice))
            ->assertOk();

        $this->assertTrue(SystemActivityLog::query()->where('action', 'document.downloaded')->exists());
    }

    public function test_delivery_status_updates_on_resend(): void
    {
        Mail::fake();
        $this->stubPdf();

        [, $tenant] = $this->userWithTenant();
        $invoice = $this->invoiceFor($tenant);
        $service = app(DocumentDeliveryService::class);

        $service->sendEmail($invoice);
        $invoice->refresh();
        $this->assertSame('sent', $invoice->delivery_status);

        $service->sendEmail($invoice->fresh(), null, true);
        $invoice->refresh();
        $this->assertSame('resent', $invoice->delivery_status);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'document.resent')->exists());
    }

    public function test_failed_email_stores_readable_error(): void
    {
        Mail::fake();
        [, $tenant] = $this->userWithTenant(null);
        $tenant->update(['billing_email' => null]);
        $invoice = $this->invoiceFor($tenant->fresh(), ['manual_client_email' => null]);

        $result = app(DocumentDeliveryService::class)->sendEmail($invoice);

        $this->assertFalse($result['success']);
        $invoice->refresh();
        $this->assertSame('failed', $invoice->delivery_status);
        $this->assertNotEmpty($invoice->last_delivery_error);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'document.email_failed')->exists());
    }

    public function test_recipient_override_is_used(): void
    {
        Mail::fake();
        $this->stubPdf();

        [, $tenant] = $this->userWithTenant('billing@tenant.test');
        $invoice = $this->invoiceFor($tenant);

        app(DocumentDeliveryService::class)->sendEmail($invoice, 'override@client.com');

        Mail::assertSent(FinancialDocumentMail::class, fn ($m) => $m->hasTo('override@client.com'));
    }
}
