<?php

namespace Tests\Feature;

use App\Jobs\Billing\GenerateFinancialDocumentPdfJob;
use App\Jobs\Billing\ProcessRecurringBillingJob;
use App\Jobs\Billing\SendFinancialDocumentEmailJob;
use App\Jobs\Servers\SyncServerTelemetryJob;
use App\Jobs\Webhooks\DeliverWebhookEventJob;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Queue\QueueName;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueueOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_document_email_job_uses_emails_queue(): void
    {
        $job = new SendFinancialDocumentEmailJob(1, 1, 'billing@example.com');

        $this->assertSame(QueueName::EMAILS, $job->queue);
    }

    public function test_pdf_generation_job_uses_pdf_queue(): void
    {
        $job = new GenerateFinancialDocumentPdfJob(1);

        $this->assertSame(QueueName::PDF, $job->queue);
    }

    public function test_webhook_delivery_job_uses_webhooks_queue(): void
    {
        $job = new DeliverWebhookEventJob('event-uuid', false, 'delivery-uuid');

        $this->assertSame(QueueName::WEBHOOKS, $job->queue);
        $this->assertSame(5, $job->tries);
    }

    public function test_telemetry_sync_job_uses_telemetry_queue(): void
    {
        $job = new SyncServerTelemetryJob(1);

        $this->assertSame(QueueName::TELEMETRY, $job->queue);
    }

    public function test_recurring_billing_command_dispatches_billing_job(): void
    {
        Bus::fake();

        $this->artisan('billing:process-recurring')->assertSuccessful();

        Bus::assertDispatched(ProcessRecurringBillingJob::class, fn (ProcessRecurringBillingJob $job) => $job->queue === QueueName::BILLING);
    }

    public function test_queue_monitoring_page_loads_without_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('monitoring.queues'));

        $response->assertOk();
        $response->assertSee('Queue breakdown');
        $response->assertSee('Operational guidance');
        $response->assertSee('Live pipeline');
        $response->assertDontSee('REDIS_PASSWORD', false);
        $response->assertDontSee('dev-admin-token', false);

        $redisPassword = (string) env('REDIS_PASSWORD', '');
        if ($redisPassword !== '') {
            $response->assertDontSee($redisPassword, false);
        }
    }

    public function test_queue_cards_render_with_purpose_labels(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('monitoring.queues'));

        $response->assertOk();
        $response->assertSee('critical');
        $response->assertSee('payments');
        $response->assertSee('Payment capture & reconciliation');
        $response->assertSee('Transactional email delivery');
        $response->assertSee('php artisan queue:work redis --queue=critical --tries=3', false);
    }

    public function test_failed_jobs_are_listed_on_monitoring_page(): void
    {
        $user = User::factory()->create();
        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'redis',
            'queue' => QueueName::EMAILS,
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Billing\\SendFinancialDocumentEmailJob']),
            'exception' => "RuntimeException: SMTP connection refused\n#0 /app/Jobs/SendFinancialDocumentEmailJob.php:42",
            'failed_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)
            ->get(route('monitoring.queues'));

        $response->assertOk();
        $response->assertSee('SendFinancialDocumentEmailJob');
        $response->assertSee(QueueName::EMAILS);
        $response->assertSee('RuntimeException: SMTP connection refused');
        $response->assertDontSee('/app/Jobs/SendFinancialDocumentEmailJob.php:42', false);
    }

    public function test_empty_states_render_when_queues_and_failures_are_clear(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('monitoring.queues'));

        $response->assertOk();
        $response->assertSee('Queues are clear');
        $response->assertSee('All queues idle');
        $response->assertSee('No failed jobs');
    }

    public function test_retry_and_forget_routes_require_monitoring_sync_permission(): void
    {
        config(['rbac.legacy_open_access' => false]);
        $this->seed(RbacBootstrapSeeder::class);

        $user = User::factory()->create();
        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'redis',
            'queue' => QueueName::DEFAULT,
            'payload' => json_encode(['displayName' => 'App\\Jobs\\ExampleJob']),
            'exception' => 'Exception: test failure',
            'failed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('monitoring.failed-jobs.retry', $uuid))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('monitoring.failed-jobs.forget', $uuid))
            ->assertForbidden();
    }

    public function test_document_email_job_can_be_dispatched_to_emails_queue(): void
    {
        Queue::fake();

        SendFinancialDocumentEmailJob::dispatch(99, 5, 'client@example.com');

        Queue::assertPushed(SendFinancialDocumentEmailJob::class, fn (SendFinancialDocumentEmailJob $job) => $job->queue === QueueName::EMAILS);
    }

    public function test_technical_exception_details_hidden_without_sync_permission(): void
    {
        config(['rbac.legacy_open_access' => false]);
        $this->seed(RbacBootstrapSeeder::class);

        $user = $this->userWithPermissions(['monitoring.view']);
        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'redis',
            'queue' => QueueName::BILLING,
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Billing\\ProcessRecurringBillingJob']),
            'exception' => "ErrorException: Undefined index\n#0 /secret/path/ProcessRecurringBillingJob.php:99",
            'failed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('monitoring.queues'))
            ->assertOk()
            ->assertSee('Undefined index')
            ->assertDontSee('View technical details')
            ->assertDontSee('/secret/path/ProcessRecurringBillingJob.php:99', false);

        $this->actingAs($user)
            ->get(route('monitoring.failed-jobs.details', $uuid))
            ->assertForbidden();
    }

    public function test_technical_details_endpoint_returns_exception_for_sync_permission(): void
    {
        config(['rbac.legacy_open_access' => false]);
        $this->seed(RbacBootstrapSeeder::class);

        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'redis',
            'queue' => QueueName::BILLING,
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Billing\\ProcessRecurringBillingJob']),
            'exception' => "ErrorException: Undefined index\n#0 /secret/path/ProcessRecurringBillingJob.php:99",
            'failed_at' => now(),
        ]);

        $this->actingAs($this->userWithPermissions(['monitoring.view', 'monitoring.sync']))
            ->getJson(route('monitoring.failed-jobs.details', $uuid))
            ->assertOk()
            ->assertJsonPath('uuid', $uuid)
            ->assertJsonFragment(['exception' => "ErrorException: Undefined index\n#0 /secret/path/ProcessRecurringBillingJob.php:99"]);
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    private function userWithPermissions(array $permissionCodes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Monitoring Tester',
            'code' => 'monitoring_tester_'.uniqid(),
            'status' => 'active',
        ]);
        $role->permissions()->sync(Permission::query()->whereIn('code', $permissionCodes)->pluck('id'));

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        return $user->fresh();
    }
}
