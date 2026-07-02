<?php

namespace Tests\Feature;

use App\Domain\Billing\PaymentsGatewayTransactionImporter;
use App\Jobs\Backups\RunBackupJob;
use App\Jobs\Billing\ReconcilePaymentsBatchJob;
use App\Jobs\Deployments\ProcessDeploymentWebhookJob;
use App\Models\DeploymentIntegration;
use App\Models\DeploymentWebhookEvent;
use App\Models\TenantPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class IntegrationWiringTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    public function test_ops_health_command_runs(): void
    {
        $this->artisan('ops:health --json')
            ->expectsOutputToContain('"checks"');
    }

    public function test_payments_gateway_webhook_imports_transaction(): void
    {
        Config::set('payment_gateway.webhook_secret', 'test-webhook-secret');

        [, , $tenant] = $this->createTenantWithSubscription('Webhook Tenant', [
            'external_key' => 'wh-tenant',
            'payments_gateway_tenant_uuid' => (string) Str::uuid(),
        ]);

        $uuid = (string) Str::uuid();

        $response = $this->postJson('/api/v1/payments-gateway/webhooks', [
            'event' => 'transaction.completed',
            'transaction' => [
                'uuid' => $uuid,
                'tenant_uuid' => $tenant->payments_gateway_tenant_uuid,
                'amount' => 1500,
                'currency' => 'KES',
                'status' => 'success',
                'transaction_type' => 'mpesa_stk',
                'mpesa_receipt_number' => 'ABC123XYZ',
                'phone_number' => '254712345678',
                'processed_at' => now()->toIso8601String(),
            ],
        ], [
            'Authorization' => 'Bearer test-webhook-secret',
        ]);

        $response->assertOk()->assertJsonPath('transaction_uuid', $uuid);

        $this->assertDatabaseHas('tenant_payments', [
            'gateway_transaction_uuid' => $uuid,
            'tenant_id' => $tenant->id,
            'reference' => 'ABC123XYZ',
            'status' => 'successful',
        ]);
    }

    public function test_payments_gateway_importer_is_idempotent(): void
    {
        [, , $tenant] = $this->createTenantWithSubscription('Idempotent Tenant', [
            'external_key' => 'idem-tenant',
            'payments_gateway_tenant_uuid' => (string) Str::uuid(),
        ]);

        $uuid = (string) Str::uuid();
        $payload = [
            'uuid' => $uuid,
            'tenant_uuid' => $tenant->payments_gateway_tenant_uuid,
            'amount' => 500,
            'currency' => 'KES',
            'status' => 'pending',
        ];

        $importer = app(PaymentsGatewayTransactionImporter::class);
        $first = $importer->import($payload);
        $payload['status'] = 'success';
        $second = $importer->import($payload);

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id);
        $this->assertSame('successful', $second?->status);
        $this->assertSame(1, TenantPayment::query()->where('gateway_transaction_uuid', $uuid)->count());
    }

    public function test_deployment_webhook_accepts_signed_payload(): void
    {
        Bus::fake([ProcessDeploymentWebhookJob::class]);

        Config::set('deployments.webhook_secret', 'deploy-secret');

        $integration = DeploymentIntegration::query()->create([
            'provider' => 'github',
            'name' => 'GitHub Test',
            'status' => 'connected',
            'settings' => ['webhook_secret' => 'deploy-secret', 'auto_deploy' => true],
        ]);

        $payload = [
            'ref' => 'refs/heads/main',
            'repository' => ['full_name' => 'prady/demo-app'],
            'head_commit' => ['id' => 'abc1234567890'],
        ];

        $response = $this->postJson(
            '/api/v1/deployments/webhooks/'.$integration->id,
            $payload,
            ['Authorization' => 'Bearer deploy-secret'],
        );

        $response->assertAccepted();
        $this->assertSame(1, DeploymentWebhookEvent::query()->count());
        Bus::assertDispatched(ProcessDeploymentWebhookJob::class);
    }

    public function test_payment_reconcile_dispatches_batch_job(): void
    {
        Bus::fake([ReconcilePaymentsBatchJob::class]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('payments.reconcile'))
            ->assertRedirect(route('payments.index'));

        Bus::assertDispatched(ReconcilePaymentsBatchJob::class);
    }

    public function test_backup_run_dispatches_job(): void
    {
        Bus::fake([RunBackupJob::class]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('backups.run'))
            ->assertRedirect(route('backups.index'));

        Bus::assertDispatched(RunBackupJob::class);
    }
}
