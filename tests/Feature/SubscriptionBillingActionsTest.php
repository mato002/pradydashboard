<?php

namespace Tests\Feature;

use App\Domain\Billing\SubscriptionBillingService;
use App\Jobs\Billing\GenerateSubscriptionInvoicesJob;
use App\Jobs\Billing\RenewSubscriptionsJob;
use App\Models\Project;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SubscriptionBillingActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DocumentTemplateSeeder::class);
    }

    private function seedSubscription(): TenantSubscription
    {
        $project = Project::query()->create([
            'name' => 'CRM',
            'domain' => 'crm.test',
        ]);

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Acme Ltd',
            'status' => 'active',
        ]);

        $plan = SaasPlan::query()->create([
            'slug' => 'starter',
            'name' => 'Starter',
            'tier' => 'starter',
            'monthly_price' => 5000,
            'annual_price' => 50000,
            'currency' => 'KES',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'plan_name' => 'Starter',
            'product_name' => 'CRM',
            'amount' => 5000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addDays(3),
            'status' => 'active',
            'auto_renew' => true,
        ]);
    }

    public function test_renew_subscription_creates_invoice_without_extending_period(): void
    {
        $subscription = $this->seedSubscription();
        $previousEnd = $subscription->current_period_end->copy();
        $previousStatus = $subscription->status;
        $tenantStatus = $subscription->tenant->status;

        $invoice = app(SubscriptionBillingService::class)->renew($subscription);

        $this->assertNotNull($invoice);
        $this->assertSame('sent', $invoice->status);
        $this->assertDatabaseHas('tenant_invoices', [
            'id' => $invoice->id,
            'tenant_id' => $subscription->tenant_id,
        ]);

        $subscription->refresh();
        $this->assertTrue($subscription->current_period_end->equalTo($previousEnd));
        $this->assertSame($previousStatus, $subscription->status);
        $this->assertSame($tenantStatus, $subscription->tenant->fresh()->status);
        $this->assertStringContainsString('renewal', strtolower($invoice->lineItems->first()->description));
    }

    public function test_renew_does_not_reactivate_overdue_without_payment(): void
    {
        $subscription = $this->seedSubscription();
        $subscription->update(['status' => 'overdue']);
        $subscription->tenant->update(['status' => 'overdue']);

        $invoice = app(SubscriptionBillingService::class)->renew($subscription->fresh());

        $this->assertNotNull($invoice);
        $subscription->refresh();
        $this->assertSame('overdue', $subscription->status);
        $this->assertSame('overdue', $subscription->tenant->fresh()->status);
    }

    public function test_generate_invoice_creates_tenant_invoice(): void
    {
        $subscription = $this->seedSubscription();

        $invoice = app(SubscriptionBillingService::class)->generateInvoice($subscription);

        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('tenant_invoices', [
            'id' => $invoice->id,
            'tenant_id' => $subscription->tenant_id,
        ]);
        $this->assertNotNull($invoice->subscription_period_key);
        $this->assertNotNull($invoice->statement_period_start);
        $this->assertNotNull($invoice->statement_period_end);
    }

    public function test_generate_invoice_skips_duplicate_for_same_period(): void
    {
        $subscription = $this->seedSubscription();
        $billing = app(SubscriptionBillingService::class);

        $first = $billing->generateInvoice($subscription);
        $second = $billing->generateInvoice($subscription->fresh());

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, \App\Models\TenantInvoice::query()
            ->where('tenant_id', $subscription->tenant_id)
            ->where('generated_by', 'subscription-billing')
            ->whereNotIn('status', ['cancelled', 'void'])
            ->count());
    }

    public function test_renewal_invoice_skips_duplicate_for_same_window(): void
    {
        $subscription = $this->seedSubscription();
        $billing = app(SubscriptionBillingService::class);

        $first = $billing->generateRenewalInvoice($subscription);
        $second = $billing->generateRenewalInvoice($subscription->fresh());

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, \App\Models\TenantInvoice::query()
            ->where('subscription_period_key', $first->subscription_period_key)
            ->count());
    }

    public function test_fleet_renew_dispatches_job(): void
    {
        Bus::fake([RenewSubscriptionsJob::class]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('subscriptions.renew'))
            ->assertRedirect(route('subscriptions.index'))
            ->assertSessionHas('status');

        Bus::assertDispatched(RenewSubscriptionsJob::class);

        $status = session('status');
        $this->assertIsString($status);
        $this->assertTrue(
            str_contains($status, 'queue') || str_contains($status, 'sync'),
            'Flash should mention queue processing, got: '.$status
        );
    }

    public function test_fleet_invoice_dispatches_job(): void
    {
        Bus::fake([GenerateSubscriptionInvoicesJob::class]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('subscriptions.invoice'))
            ->assertRedirect(route('subscriptions.index'))
            ->assertSessionHas('status');

        Bus::assertDispatched(GenerateSubscriptionInvoicesJob::class);

        $status = session('status');
        $this->assertIsString($status);
        $this->assertTrue(
            str_contains($status, 'queue') || str_contains($status, 'sync'),
            'Flash should mention queue processing, got: '.$status
        );
    }

    public function test_subscription_billing_jobs_are_scheduled(): void
    {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $descriptions = collect($schedule->events())
            ->map(fn ($event) => (string) ($event->description ?? ''))
            ->implode(' ');

        $this->assertStringContainsString('RenewSubscriptionsJob', $descriptions);
        $this->assertStringContainsString('GenerateSubscriptionInvoicesJob', $descriptions);
    }

    public function test_create_page_accepts_plan_query_param(): void
    {
        $user = User::factory()->create();
        $plan = SaasPlan::query()->create([
            'slug' => 'pro',
            'name' => 'Professional',
            'tier' => 'professional',
            'monthly_price' => 15000,
            'annual_price' => 150000,
            'currency' => 'KES',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('subscriptions.create', ['saas_plan_id' => $plan->id, 'upgrade' => 1]))
            ->assertOk()
            ->assertSee(__('Upgrade mode'));
    }

    public function test_single_subscription_suspend_route(): void
    {
        $subscription = $this->seedSubscription();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('subscriptions.subscription.suspend', $subscription))
            ->assertRedirect(route('subscriptions.index'));

        $subscription->refresh();
        $this->assertSame('suspended', $subscription->status);
    }

    public function test_single_subscription_renew_route(): void
    {
        $subscription = $this->seedSubscription();
        $previousEnd = $subscription->current_period_end->copy();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('subscriptions.subscription.renew', $subscription));

        $subscription->refresh();
        $this->assertTrue($subscription->current_period_end->equalTo($previousEnd));

        $invoice = \App\Models\TenantInvoice::query()
            ->where('tenant_id', $subscription->tenant_id)
            ->latest('id')
            ->first();

        $this->assertNotNull($invoice);
        $response->assertRedirect(route('invoices.show', $invoice));
    }
}
