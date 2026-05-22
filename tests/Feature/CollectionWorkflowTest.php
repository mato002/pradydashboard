<?php

namespace Tests\Feature;

use App\Domain\Billing\CollectionReminderService;
use App\Domain\Billing\CollectionWorkflowService;
use App\Domain\Billing\FinancialOperationsQuery;
use App\Domain\Billing\PdfGenerator;
use App\Domain\Billing\TenantCollectionsQuery;
use App\Domain\Rbac\RbacScopeFilter;
use App\Mail\PaymentReminderMail;
use App\Models\BillingAutomationRule;
use App\Models\CollectionNote;
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

class CollectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', [
            'default_currency' => 'KES',
            'company_legal_name' => 'Prady Ltd',
            'payment_instructions' => 'Pay to account 123',
        ]);

        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform()->update([
            'reminder_after_days' => 1,
            'penalty_after_days' => 30,
            'suspension_after_days' => 60,
            'grace_period_days' => 7,
        ]);
        Storage::fake('local');
    }

    private function overdueInvoice(Tenant $tenant): TenantInvoice
    {
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-COL-'.uniqid(),
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 5000,
            'status' => 'sent',
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 5000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 5000,
        ]);

        return $invoice->fresh(['tenant', 'lineItems']);
    }

    private function tenant(): Tenant
    {
        $project = Project::query()->create(['name' => 'Col', 'domain' => 'col.test', 'currency' => 'KES']);

        return Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Collections Co',
            'billing_email' => 'collections@tenant.test',
            'status' => 'active',
        ]);
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

    public function test_overdue_invoice_appears_in_collections(): void
    {
        $tenant = $this->tenant();
        $this->overdueInvoice($tenant);

        $overview = app(FinancialOperationsQuery::class)->collectionsOverview(app(RbacScopeFilter::class));

        $this->assertGreaterThanOrEqual(1, $overview['overdue']->count());
    }

    public function test_collection_note_creation(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());

        $note = app(CollectionWorkflowService::class)->addNote($invoice, [
            'note' => 'Called client, no answer.',
            'follow_up_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->assertDatabaseHas('collection_notes', [
            'id' => $note->id,
            'tenant_invoice_id' => $invoice->id,
            'status' => 'open',
        ]);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.note_added')->exists());
    }

    public function test_promise_to_pay_creation(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());

        app(CollectionWorkflowService::class)->recordPromiseToPay($invoice, [
            'promise_to_pay_date' => now()->addDays(7)->toDateString(),
            'promised_amount' => 2500,
            'note' => 'Will pay half next week.',
        ]);

        $this->assertDatabaseHas('collection_notes', [
            'tenant_invoice_id' => $invoice->id,
            'outcome' => 'promised_payment',
            'status' => 'open',
        ]);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.promise_to_pay')->exists());
    }

    public function test_reminder_email_sends(): void
    {
        Mail::fake();
        $this->stubPdf();

        $invoice = $this->overdueInvoice($this->tenant());
        $result = app(CollectionReminderService::class)->sendReminder($invoice);

        $this->assertTrue($result['success']);
        Mail::assertSent(PaymentReminderMail::class, fn ($m) => $m->hasTo('collections@tenant.test'));
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.reminder_sent')->exists());
        $this->assertSame(1, $invoice->fresh()->reminder_count);
    }

    public function test_duplicate_reminder_prevention(): void
    {
        Mail::fake();
        $this->stubPdf();

        $invoice = $this->overdueInvoice($this->tenant());
        $service = app(CollectionReminderService::class);

        $service->sendReminder($invoice);
        $invoice->refresh();

        $this->assertFalse($service->shouldSendAutomatedReminder($invoice));
    }

    public function test_follow_up_completion(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $note = CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'note_type' => 'general',
            'body' => 'Follow up call',
            'note' => 'Follow up call',
            'status' => 'open',
            'follow_up_date' => now()->subDay()->toDateString(),
        ]);

        app(CollectionWorkflowService::class)->completeFollowUp($note);

        $this->assertSame('completed', $note->fresh()->status);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.follow_up_completed')->exists());
    }

    public function test_tenant_billing_tab_shows_overdue_collections(): void
    {
        $tenant = $this->tenant();
        $this->overdueInvoice($tenant);

        $data = app(TenantCollectionsQuery::class)->forTenant($tenant);

        $this->assertGreaterThanOrEqual(1, $data['overdue_invoices']->count());
        $this->assertGreaterThanOrEqual(1, $data['unpaid_invoices']->count());
    }

    public function test_billing_send_reminders_command(): void
    {
        Mail::fake();
        $this->stubPdf();

        $invoice = $this->overdueInvoice($this->tenant());
        $invoice->update(['last_reminder_at' => null, 'reminder_count' => 0]);

        $this->artisan('billing:send-reminders')->assertSuccessful();

        $this->assertGreaterThanOrEqual(1, $invoice->fresh()->reminder_count);
    }

    public function test_collections_tab_renders_overdue_invoice(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('invoices.index', ['tab' => 'collections']))
            ->assertOk()
            ->assertSee($invoice->invoice_number, false)
            ->assertSee(__('Overdue invoices'), false)
            ->assertSee(__('Aging (days overdue)'), false);
    }

    public function test_http_collection_note_creation(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('invoices.collection-notes.store', $invoice), [
                'note' => 'Left voicemail for AP team.',
                'follow_up_date' => now()->addDays(2)->toDateString(),
                'outcome' => 'no_response',
                'status' => 'open',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('collection_notes', [
            'tenant_invoice_id' => $invoice->id,
            'outcome' => 'no_response',
            'status' => 'open',
        ]);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.note_added')->exists());
    }

    public function test_http_promise_to_pay(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('invoices.promise-to-pay', $invoice), [
                'promise_to_pay_date' => now()->addDays(5)->toDateString(),
                'promised_amount' => 5000,
                'note' => 'Committed to pay Friday.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('collection_notes', [
            'tenant_invoice_id' => $invoice->id,
            'outcome' => 'promised_payment',
        ]);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.promise_to_pay')->exists());
    }

    public function test_http_complete_follow_up(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $note = CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'note_type' => 'general',
            'body' => 'Scheduled callback',
            'note' => 'Scheduled callback',
            'status' => 'open',
            'follow_up_date' => now()->subDay()->toDateString(),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('invoices.collection-notes.complete', [$invoice, $note]))
            ->assertRedirect();

        $this->assertSame('completed', $note->fresh()->status);
        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.follow_up_completed')->exists());
    }

    public function test_tenant_billing_tab_shows_overdue_collection_info(): void
    {
        $tenant = $this->tenant();
        $invoice = $this->overdueInvoice($tenant);
        $user = User::factory()->create();

        CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'note_type' => 'promise',
            'body' => 'Will pay next week',
            'note' => 'Will pay next week',
            'status' => 'open',
            'outcome' => 'promised_payment',
            'promise_to_pay_date' => now()->addDays(7)->toDateString(),
            'promised_amount' => 5000,
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing']))
            ->assertOk()
            ->assertSee(__('Collections overview'), false)
            ->assertSee($invoice->invoice_number, false)
            ->assertSee(__('Open collection notes'), false)
            ->assertSee(__('Promise to pay'), false);
    }

    public function test_http_mark_disputed_and_escalate(): void
    {
        $invoice = $this->overdueInvoice($this->tenant());
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('invoices.disputed', $invoice), ['note' => 'Amount incorrect on line 2'])
            ->assertRedirect();

        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.invoice_disputed')->exists());

        $this->actingAs($user)
            ->post(route('invoices.escalate', $invoice), ['note' => 'Needs manager approval'])
            ->assertRedirect();

        $this->assertTrue(SystemActivityLog::query()->where('action', 'collection.escalated')->exists());
    }
}
