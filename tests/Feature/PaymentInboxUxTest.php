<?php

namespace Tests\Feature;

use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\BillingAutomationRule;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use App\Models\User;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInboxUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setJson('platform.billing', ['default_currency' => 'KES']);
        $this->seed(DocumentTemplateSeeder::class);
        BillingAutomationRule::platform();
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    /** @return array{0: Tenant, 1: TenantInvoice} */
    private function tenantWithInvoice(float $total = 8000, string $number = 'INV-UX-001'): array
    {
        $project = Project::query()->create(['name' => 'UX Co', 'domain' => 'ux.test', 'currency' => 'KES']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'UX Tenant',
            'billing_email' => 'ux@tenant.test',
            'billing_phone' => '254711223344',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => $number,
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => $total,
            'total' => $total,
            'amount_due' => $total,
            'status' => 'sent',
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => $total,
            'line_total' => $total,
        ]);

        return [$tenant, $invoice->fresh()];
    }

    public function test_suggestions_rendered_in_payment_inbox(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice(5000, 'INV-UX-SUG');

        app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $tenant->id,
            'amount' => 5000,
            'reference' => 'Payment for INV-UX-SUG',
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
            'payer_name' => 'UX Tenant',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('invoices.index', ['tab' => 'payments', 'reconciliation_status' => 'unreconciled']));

        $response->assertOk();
        $response->assertSee('INV-UX-SUG', false);
        $response->assertSee(__('Suggested matches'), false);
        $response->assertSee(__('pts'), false);
        $response->assertSee('M-Pesa', false);
    }

    public function test_quick_match_works(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice(3000, 'INV-UX-MATCH');

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $tenant->id,
            'amount' => 3000,
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
        ]);

        $this->actingAs($this->admin())
            ->post(route('invoices.payments.match', $payment), [
                'invoice_id' => $invoice->id,
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertContains($payment->fresh()->reconciliation_status, ['matched', 'partially_matched']);
    }

    public function test_split_modal_submits_allocations(): void
    {
        [$tenant, $inv1] = $this->tenantWithInvoice(4000, 'INV-UX-S1');
        $inv2 = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-UX-S2',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 2000,
            'total' => 2000,
            'amount_due' => 2000,
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        $payment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $tenant->id,
            'amount' => 6000,
            'payment_date' => now()->toDateString(),
            'source' => 'cash',
        ]);

        $this->actingAs($this->admin())
            ->from(route('invoices.index', ['tab' => 'payments']))
            ->post(route('invoices.payments.split', $payment), [
                'allocations' => [
                    ['invoice_id' => $inv1->id, 'amount' => 4000],
                    ['invoice_id' => $inv2->id, 'amount' => 2000],
                ],
            ])
            ->assertRedirect(route('invoices.index', ['tab' => 'payments']))
            ->assertSessionHasNoErrors();

        $payment->refresh();
        $this->assertSame(2, $payment->allocations()->count());
        $this->assertContains($payment->reconciliation_status, ['matched', 'partially_matched']);
    }

    public function test_duplicate_badge_appears(): void
    {
        app(PaymentRecorderService::class)->recordUnreconciled([
            'amount' => 1500,
            'reference' => 'DUP-BADGE-001',
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
        ]);

        app(PaymentRecorderService::class)->recordUnreconciled([
            'amount' => 1500,
            'reference' => 'DUP-BADGE-001',
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('invoices.index', ['tab' => 'payments', 'reconciliation_status' => 'unreconciled']));

        $response->assertOk();
        $response->assertSee(__('Possible duplicate'), false);
        $response->assertSee(__('Mark duplicate'), false);
    }

    public function test_filters_work(): void
    {
        [$tenant] = $this->tenantWithInvoice();

        app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $tenant->id,
            'amount' => 100,
            'reference' => 'FILTER-UNRECON-MPESA',
            'payment_date' => now()->toDateString(),
            'source' => 'mpesa',
        ]);

        $matchedPayment = app(PaymentRecorderService::class)->recordUnreconciled([
            'tenant_id' => $tenant->id,
            'amount' => 200,
            'reference' => 'FILTER-MATCHED-CASH',
            'payment_date' => now()->subDays(2)->toDateString(),
            'source' => 'cash',
        ]);
        $matchedPayment->update(['reconciliation_status' => 'matched', 'matched_at' => now()]);

        $unreconciled = $this->actingAs($this->admin())
            ->get(route('invoices.index', ['tab' => 'payments', 'reconciliation_status' => 'unreconciled']));
        $unreconciled->assertOk();
        $unreconciled->assertSee('FILTER-UNRECON-MPESA', false);
        $unreconciled->assertDontSee('FILTER-MATCHED-CASH', false);

        $matched = $this->actingAs($this->admin())
            ->get(route('invoices.index', [
                'tab' => 'payments',
                'reconciliation_status' => 'matched',
                'source' => 'cash',
            ]));
        $matched->assertOk();
        $matched->assertSee('FILTER-MATCHED-CASH', false);
        $matched->assertDontSee('FILTER-UNRECON-MPESA', false);

        $byTenant = $this->actingAs($this->admin())
            ->get(route('invoices.index', [
                'tab' => 'payments',
                'tenant_id' => $tenant->id,
                'date_from' => now()->subDays(1)->toDateString(),
            ]));
        $byTenant->assertOk();
        $byTenant->assertSee('FILTER-UNRECON-MPESA', false);
    }

    public function test_payment_row_shows_payer_and_narration(): void
    {
        app(PaymentRecorderService::class)->recordUnreconciled([
            'payer_name' => 'Jane Payer',
            'payer_phone' => '254700111222',
            'payer_email' => 'jane@example.com',
            'narration' => 'Office rent March',
            'amount' => 900,
            'payment_date' => now()->toDateString(),
            'source' => 'bank_transfer',
            'reference' => 'BNK-UX-99',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('invoices.index', ['tab' => 'payments']));

        $response->assertOk();
        $response->assertSee('Jane Payer', false);
        $response->assertSee('254700111222', false);
        $response->assertSee('jane@example.com', false);
        $response->assertSee('Office rent March', false);
        $response->assertSee('BNK-UX-99', false);
    }
}
