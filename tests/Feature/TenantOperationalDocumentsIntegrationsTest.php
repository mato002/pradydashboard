<?php

namespace Tests\Feature;

use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\OperationalDocument;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantOperationalDocumentsIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_upload_store_and_download(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Doc Product', 'domain' => 'doc.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Doc Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('tenants.documents.store', $tenant), [
                'title' => 'Master Agreement',
                'document_type' => 'contract',
                'status' => 'signed',
                'file' => $file,
                'signed_date' => now()->toDateString(),
                'expiry_date' => now()->addYear()->toDateString(),
            ])
            ->assertRedirect();

        $document = OperationalDocument::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($document);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($user)
            ->get(route('tenants.documents.download', [$tenant, $document]))
            ->assertOk();
    }

    public function test_expiring_document_warning_on_documents_tab(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Expire Product', 'domain' => 'exp.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Expire Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        OperationalDocument::query()->create([
            'tenant_id' => $tenant->id,
            'document_type' => 'nda',
            'title' => 'NDA 2026',
            'file_path' => 'operational-documents/'.$tenant->id.'/nda.pdf',
            'status' => 'signed',
            'expiry_date' => now()->addDays(10),
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=documents')
            ->assertOk()
            ->assertSee(__('Expiring documents'))
            ->assertSee('NDA 2026');
    }

    public function test_integration_secret_masking_does_not_overwrite(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'API Product', 'domain' => 'api.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'API Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.store', [$tenant, $subscription]), [
                'integration_category' => 'provider',
                'service_type' => 'custom_api',
                'display_name' => 'Custom API',
                'api_secret' => 'real-secret-token-12345',
                'endpoint_url' => 'https://httpbin.org/status/200',
            ]);

        $integration = TenantProjectServiceIntegration::query()->first();
        $originalEncrypted = $integration->getAttributes()['api_secret'];
        $this->assertNotNull($originalEncrypted);

        $this->actingAs($user)
            ->put(route('tenants.project-subscriptions.integrations.update', [$tenant, $subscription, $integration]), [
                'integration_category' => 'provider',
                'service_type' => 'custom_api',
                'display_name' => 'Custom API Updated',
                'api_secret' => ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER,
                'endpoint_url' => 'https://httpbin.org/status/200',
                'status' => 'active',
            ])
            ->assertRedirect();

        $integration->refresh();
        $this->assertSame($originalEncrypted, $integration->getAttributes()['api_secret']);
        $this->assertSame('Custom API Updated', $integration->display_name);
    }

    public function test_integration_test_updates_status(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Test Product', 'domain' => 'test.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Test Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'service_type' => 'smtp_email',
            'display_name' => 'SMTP',
            'endpoint_url' => 'https://httpbin.org/status/200',
            'status' => 'not_configured',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.test', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $integration->refresh();
        $this->assertSame('pass', $integration->last_test_status);
        $this->assertSame('active', $integration->status);
        $this->assertNotNull($integration->last_tested_at);
    }

    public function test_documents_empty_state(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Empty', 'domain' => 'empty.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Empty Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=documents')
            ->assertOk()
            ->assertSee(__('No operational documents uploaded yet.'));
    }
}
