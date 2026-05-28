<?php

namespace Tests\Feature;

use App\Domain\Billing\BillingSummary;
use App\Domain\Billing\PaymentReconciliationService;
use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Servers\ServerTelemetrySyncService;
use App\Domain\Tenancy\TenantCommandCenter;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\Product;
use App\Models\Server;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use App\Models\User;
use App\Support\Cache\CacheInvalidator;
use App\Support\Cache\OperationalCache;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RedisCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['redis_cache.enabled' => true]);
        Setting::setJson('platform.billing', ['default_currency' => 'KES']);
        $this->seed(DocumentTemplateSeeder::class);
        \App\Models\BillingAutomationRule::platform();
    }

    public function test_billing_summary_is_cached_and_invalidates_after_invoice_change(): void
    {
        Cache::flush();

        [, $tenant] = $this->tenantWithProject('Cache Co');

        $summary = app(BillingSummary::class);
        $first = $summary->platform();
        $second = $summary->platform();

        $this->assertSame($first['mrr'], $second['mrr']);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-CACHE-001',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 5000,
            'status' => 'overdue',
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDays(1)->toDateString(),
        ]);

        $after = $summary->platform();
        $this->assertNotSame($first['overdue_amount'], $after['overdue_amount']);
        $this->assertGreaterThan($first['overdue_amount'], $after['overdue_amount']);
    }

    public function test_tenant_command_center_cache_invalidates_after_subscription_change(): void
    {
        Cache::flush();

        [, $tenant] = $this->tenantWithProject('CC Co');
        $tenant->load(['projectSubscriptions', 'supportTickets']);

        $commandCenter = app(TenantCommandCenter::class);
        $before = $commandCenter->summary($tenant);

        $subscription = $tenant->projectSubscriptions->first();
        $subscription->update(['monthly_fee' => 25000]);

        $after = $commandCenter->summary($tenant);
        $this->assertNotSame($before['monthly_revenue'], $after['monthly_revenue']);
        $this->assertSame(25000.0, (float) $after['monthly_revenue']);
    }

    public function test_payment_reference_lock_prevents_duplicate_processing_flag(): void
    {
        Cache::flush();

        [, $invoice] = $this->tenantWithInvoice();

        $recorder = app(PaymentRecorderService::class);
        $cache = app(OperationalCache::class);

        $lockName = $cache->paymentReferenceLockKey((int) $invoice->tenant_id, 'REF-LOCK-001');
        $this->assertNotNull($lockName);
        $this->assertStringNotContainsString('REF-LOCK-001', $lockName);

        $held = false;
        $cache->lock($lockName, 30, function () use ($recorder, $invoice, &$held): void {
            $held = true;

            $this->expectException(\Illuminate\Validation\ValidationException::class);
            $recorder->recordForInvoice($invoice, [
                'amount' => 1000,
                'reference' => 'REF-LOCK-001',
                'payment_date' => now()->toDateString(),
            ]);
        });

        $this->assertTrue($held);
    }

    public function test_server_sync_lock_blocks_overlapping_sync(): void
    {
        Cache::flush();

        $server = Server::query()->create([
            'name' => 'Lock Server',
            'provider' => 'manual',
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'telemetry_mode' => 'basic',
        ]);

        $cache = app(OperationalCache::class);
        $blocked = false;

        $cache->lock('server:sync:'.$server->id, 30, function () use ($cache, $server, &$blocked): void {
            $result = $cache->lock('server:sync:'.$server->id, 30, fn () => ['ok' => true]);
            $blocked = $result === null;
        });

        $this->assertTrue($blocked);
    }

    public function test_cache_keys_do_not_contain_secrets(): void
    {
        $cache = app(OperationalCache::class);

        $referenceKey = $cache->paymentReferenceLockKey(1, 'SECRET-REF-12345');
        $this->assertNotNull($referenceKey);
        $this->assertStringNotContainsString('SECRET-REF-12345', $referenceKey);

        $billingKey = $cache->key('billing', 'summary', 'global');
        $this->assertStringStartsWith('billing:summary:', $billingKey);
    }

    public function test_operational_cache_bypasses_when_disabled(): void
    {
        config(['redis_cache.enabled' => false]);

        $cache = app(OperationalCache::class);
        $calls = 0;

        $result = $cache->remember('billing', 'summary', 60, function () use (&$calls) {
            $calls++;

            return ['mrr' => 1];
        });

        $this->assertSame(1, $calls);
        $this->assertSame(['mrr' => 1], $result);
    }

    public function test_cache_invalidator_bumps_billing_version(): void
    {
        Cache::flush();

        $cache = app(OperationalCache::class);
        $before = $cache->key('billing', 'summary', 'global');

        app(CacheInvalidator::class)->invalidateBillingSummaries();

        $after = $cache->key('billing', 'summary', 'global');
        $this->assertNotSame($before, $after);
    }

    public function test_redis_health_command_runs(): void
    {
        $this->artisan('redis:health')
            ->assertSuccessful();
    }

    /**
     * @return array{0: Project, 1: Tenant}
     */
    private function tenantWithProject(string $name = 'Cache Co'): array
    {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'status' => 'active',
        ]);

        $project = Project::query()->create([
            'name' => $name,
            'domain' => str($name)->slug().'.test',
            'currency' => 'KES',
            'product_id' => $product->id,
        ]);

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'product_id' => $product->id,
            'company_name' => $name.' Tenant',
            'status' => 'active',
            'subscription_amount' => 10000,
        ]);

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant, $project);

        return [$project, $tenant];
    }

    /**
     * @return array{0: Tenant, 1: TenantInvoice}
     */
    private function tenantWithInvoice(): array
    {
        [, $tenant] = $this->tenantWithProject('Pay Co');

        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-LOCK-001',
            'document_type' => 'invoice',
            'currency' => 'KES',
            'subtotal' => 10000,
            'total' => 10000,
            'amount_due' => 10000,
            'status' => 'sent',
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->subDays(1)->toDateString(),
        ]);

        TenantInvoiceLineItem::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'item_type' => 'custom',
            'description' => 'Service',
            'quantity' => 1,
            'unit_price' => 10000,
            'line_total' => 10000,
        ]);

        return [$tenant, $invoice->fresh(['lineItems', 'tenant'])];
    }
}
