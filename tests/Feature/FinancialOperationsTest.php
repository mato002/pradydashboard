<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Domain\Billing\OverdueBillingProcessor;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Billing\ReceiptGenerator;
use App\Domain\Billing\RecurringBillingProcessor;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\InvoiceRecurringSchedule;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialOperationsTest extends TestCase
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

    private function billableTenant(): array
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Fin Ops', 'domain' => 'fin.test', 'currency' => 'KES']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Fin Co',
            'tenant_currency' => 'KES',
            'billing_email' => 'billing@fin.test',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $subscription->update(['monthly_fee' => 5000]);

        return [$user, $tenant, $subscription];
    }

    private function draftInvoice(Tenant $tenant): TenantInvoice
    {
        return TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-2026-0099',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total' => 1160,
            'amount_due' => 1160,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function test_document_snapshot_is_immutable_payload(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant);
        $invoice->lineItems()->create([
            'item_type' => 'subscription',
            'description' => 'Test line',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax_rate' => 16,
            'tax_amount' => 160,
            'line_total' => 1160,
        ]);

        $snapshot = app(DocumentSnapshotBuilder::class)->build($invoice->fresh(['lineItems', 'tenant']));
        $invoice->update(['total' => 99999]);

        $this->assertSame(1160.0, $snapshot['total']);
        $this->assertCount(1, $snapshot['line_items']);
    }

    public function test_document_template_rendering_and_finalization(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant);
        $invoice->lineItems()->create([
            'item_type' => 'subscription',
            'description' => 'Line',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
        ]);

        $document = app(DocumentFinalizer::class)->finalize($invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']));

        $this->assertNotEmpty($document->html_snapshot);
        $this->assertNotNull($invoice->fresh()->finalized_at);
        $this->assertDatabaseHas('generated_documents', ['tenant_invoice_id' => $invoice->id]);
    }

    public function test_quotation_approval_and_conversion(): void
    {
        [, $tenant] = $this->billableTenant();
        $quotation = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'QUO-2026-0001',
            'document_type' => 'quotation',
            'currency' => 'KES',
            'subtotal' => 2000,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'draft',
            'approval_status' => 'pending',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);
        $quotation->lineItems()->create([
            'item_type' => 'subscription',
            'description' => 'Quoted service',
            'quantity' => 1,
            'unit_price' => 2000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 2000,
        ]);

        $converter = app(QuotationConverter::class);
        $converter->approve($quotation->fresh());
        $invoice = $converter->convert($quotation->fresh(['lineItems']));

        $this->assertSame('invoice', $invoice->document_type);
        $this->assertNotNull($quotation->fresh()->converted_at);
        $this->assertTrue(
            SystemActivityLog::query()->where('action', 'quotation.converted')->exists()
        );
    }

    public function test_receipt_generated_on_full_payment(): void
    {
        [, $tenant] = $this->billableTenant();
        $invoice = $this->draftInvoice($tenant);
        $invoice->update(['status' => 'sent', 'total' => 500, 'amount_due' => 500, 'subtotal' => 500]);

        app(\App\Domain\Billing\InvoicePaymentRecorder::class)->record($invoice, [
            'amount' => 500,
            'payment_date' => now()->toDateString(),
            'method' => 'mpesa',
        ]);

        $this->assertTrue(
            TenantInvoice::query()->where('document_type', 'receipt')->where('tenant_id', $tenant->id)->exists()
        );
    }

    public function test_recurring_invoice_generation(): void
    {
        [, $tenant] = $this->billableTenant();
        InvoiceRecurringSchedule::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Monthly SaaS',
            'amount' => 3000,
            'tax_rate' => 0,
            'frequency' => 'monthly',
            'next_run_at' => now()->subHour(),
            'enabled' => true,
        ]);

        $generated = app(RecurringBillingProcessor::class)->processDueSchedules();
        $this->assertSame(1, $generated->count());
        $this->assertTrue(
            SystemActivityLog::query()->where('action', 'invoice.recurring_generated')->exists()
        );
    }

    public function test_overdue_automation_applies_penalty_and_logs(): void
    {
        [, $tenant] = $this->billableTenant();
        BillingAutomationRule::platform()->update(['penalty_after_days' => 1, 'reminder_after_days' => 1]);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-2026-OVERDUE',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 1000,
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'sent',
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(),
        ]);

        $counts = app(OverdueBillingProcessor::class)->process();
        $invoice = TenantInvoice::query()->where('invoice_number', 'INV-2026-OVERDUE')->first();

        $this->assertGreaterThanOrEqual(1, $counts['reminders']);
        $this->assertGreaterThan(0, (float) $invoice->penalty_amount);
    }

    public function test_financial_operations_index_loads(): void
    {
        [$user, $tenant] = $this->billableTenant();
        $this->draftInvoice($tenant);

        $this->actingAs($user)
            ->get(route('invoices.index', ['tab' => 'overview']))
            ->assertOk()
            ->assertSee(__('Financial Operations Command Center'));
    }
}
