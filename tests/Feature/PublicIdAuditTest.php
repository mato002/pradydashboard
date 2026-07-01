<?php

namespace Tests\Feature;

use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Mail\FinancialDocumentMail;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Support\PublicId\PublicIdFormat;
use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Billing\BillingDocumentType;
use App\Support\PublicId\PublicIdAuditor;
use App\Support\PublicId\PublicIdBladeRouteScanner;
use App\Support\PublicId\PublicIdRouteCoverage;
use Database\Seeders\DocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicIdAuditTest extends TestCase
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

    public function test_public_ids_audit_command_returns_ok_when_healthy(): void
    {
        $this->makeFixtures();

        $this->assertSame(0, Artisan::call('public-ids:audit'));
        $this->assertStringContainsString('OK', Artisan::output());
    }

    public function test_public_ids_audit_detects_missing_public_id(): void
    {
        $tenant = $this->makeTenant();
        $tenant->forceFill(['public_id' => null])->saveQuietly();

        $auditor = app(PublicIdAuditor::class);

        $this->assertTrue($auditor->hasIssues());
        $row = collect($auditor->audit())->firstWhere('resource', 'Tenants');
        $this->assertSame(1, $row['missing']);
    }

    public function test_public_ids_audit_detects_invalid_format(): void
    {
        $tenant = $this->makeTenant();
        $tenant->forceFill(['public_id' => 'bad!id12'])->saveQuietly();

        $auditor = app(PublicIdAuditor::class);
        $row = collect($auditor->audit())->firstWhere('resource', 'Tenants');

        $this->assertSame(1, $row['invalid_format']);
        $this->assertTrue($auditor->hasIssues());
    }

    public function test_public_ids_audit_repair_fills_missing_public_id_safely(): void
    {
        $tenant = $this->makeTenant();
        $tenant->forceFill(['public_id' => null])->saveQuietly();

        $this->assertSame(0, Artisan::call('public-ids:audit', ['--repair' => true]));

        $tenant->refresh();
        $this->assertNotNull($tenant->public_id);
        $this->assertValidPublicIdToken($tenant->public_id);
    }

    public function test_public_ids_audit_repair_does_not_overwrite_existing_public_id(): void
    {
        $tenant = $this->makeTenant();
        $original = $tenant->public_id;

        Artisan::call('public-ids:audit', ['--repair' => true]);

        $this->assertSame($original, $tenant->fresh()->public_id);
    }

    public function test_route_coverage_lists_protected_resources(): void
    {
        $coverage = app(PublicIdRouteCoverage::class)->routes();

        $this->assertNotEmpty($coverage);
        $this->assertTrue(collect($coverage)->contains(
            fn (array $row) => $row['route'] === 'invoices.preview' && $row['protected'] === true
        ));
        $this->assertTrue(app(PublicIdRouteCoverage::class)->isProtected(TenantInvoice::class));
    }

    public function test_high_risk_blade_views_do_not_use_raw_numeric_route_ids(): void
    {
        $this->assertTrue(app(PublicIdBladeRouteScanner::class)->isClean());
    }

    public function test_invoice_register_partial_displays_invoice_number_not_public_id(): void
    {
        $invoice = $this->makeInvoice('39/26');
        $invoice->load(['tenant', 'projectSubscription.project']);

        $html = view('admin.invoices.partials.register-table', [
            'invoices' => new \Illuminate\Pagination\LengthAwarePaginator([$invoice], 1, 50, 1),
            'tab' => 'invoices',
            'filterTenants' => collect([$invoice->tenant]),
            'invoiceTrend' => collect(),
            'revenueSeries' => collect(),
        ])->render();

        $this->assertStringContainsString('39/26', $html);
        $this->assertPublicIdNotUsedAsVisibleLabel($html, $invoice->public_id);
    }

    public function test_tenant_directory_payload_uses_company_name_not_public_id_label(): void
    {
        $tenant = $this->makeTenant();
        $tenant->load(['project', 'server', 'usageMetric', 'projectSubscriptions.project']);

        $presented = app(\App\Support\TenantOperationsPresenter::class)->present(
            new \Illuminate\Pagination\LengthAwarePaginator([$tenant], 1, 50, 1),
        );
        $entry = $presented['directory'][0];

        $this->assertSame($tenant->company_name, $entry['company']);
        $this->assertSame($tenant->id, $entry['id']);
        $this->assertStringContainsString($tenant->public_id, $entry['show_url']);
        $this->assertValidPublicIdToken($tenant->public_id);
        $this->assertNotSame($tenant->public_id, $entry['company']);
    }

    public function test_support_ticket_row_uses_human_reference_for_display(): void
    {
        $tenant = $this->makeTenant();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'subject' => 'Billing sync issue',
            'priority' => 'medium',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $method = new \ReflectionMethod(\App\Http\Controllers\Admin\SupportTicketsController::class, 'mapTicketRow');
        $method->setAccessible(true);
        $row = $method->invoke(app(\App\Http\Controllers\Admin\SupportTicketsController::class), $ticket);

        $this->assertSame($ticket->humanReference(), $row['id']);
        $this->assertValidPublicIdToken($row['public_id']);
        $this->assertNotSame($row['id'], $row['public_id']);
    }

    public function test_invoice_document_html_uses_invoice_number_not_public_id(): void
    {
        $invoice = $this->makeInvoice('INV-FINAL-39/26');
        $template = DocumentTemplate::query()
            ->where('type', BillingDocumentType::INVOICE)
            ->where('active', true)
            ->firstOrFail();

        $snapshot = app(DocumentSnapshotBuilder::class)->build($invoice);
        $html = app(DocumentRenderer::class)->render($template, $snapshot);

        $this->assertStringContainsString('INV-FINAL-39/26', $html);
        $this->assertStringNotContainsString($invoice->public_id, $html);
    }

    public function test_invoice_email_subject_uses_invoice_number_not_public_id(): void
    {
        $invoice = $this->makeInvoice('INV-EMAIL-39/26');
        Storage::fake('local');
        Storage::disk('local')->put('billing/test.pdf', 'pdf');

        $mail = new FinancialDocumentMail($invoice, 'billing/test.pdf');
        $subject = $mail->envelope()->subject;

        $this->assertStringContainsString('INV-EMAIL-39/26', $subject);
        $this->assertStringNotContainsString($invoice->public_id, $subject);
    }

    public function test_route_generation_uses_token_not_numeric_id_for_protected_resources(): void
    {
        $tenant = $this->makeTenant();
        $invoice = $this->makeInvoice('TOKEN-001');
        $server = Server::query()->create(['name' => 'Token Node', 'provider' => 'manual', 'status' => 'active']);
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'subject' => 'Token test',
            'priority' => 'low',
            'status' => 'open',
            'opened_at' => now(),
        ]);
        $payment = TenantPayment::query()->create([
            'tenant_id' => $tenant->id,
            'amount' => 100,
            'currency' => 'KES',
            'status' => 'successful',
            'source' => 'manual',
            'reconciliation_status' => 'unreconciled',
            'paid_at' => now(),
        ]);
        $document = OperationalDocument::query()->create([
            'tenant_id' => $tenant->id,
            'document_type' => 'contract',
            'title' => 'MSA',
            'file_path' => 'ops/test.pdf',
            'status' => 'active',
        ]);

        $cases = [
            ['route' => 'tenants.show', 'model' => $tenant, 'token' => $tenant->public_id, 'numeric' => $tenant->id],
            ['route' => 'invoices.preview', 'model' => $invoice, 'token' => $invoice->public_id, 'numeric' => $invoice->id],
            ['route' => 'servers.show', 'model' => $server, 'token' => $server->public_id, 'numeric' => $server->id],
            ['route' => 'support-tickets.show', 'model' => $ticket, 'token' => $ticket->public_id, 'numeric' => $ticket->id],
            ['route' => 'invoices.payments.match', 'model' => $payment, 'token' => $payment->public_id, 'numeric' => $payment->id],
            ['route' => 'tenants.documents.download', 'model' => [$tenant, $document], 'token' => $document->public_id, 'numeric' => $document->id],
        ];

        foreach ($cases as $case) {
            $url = is_array($case['model'])
                ? route($case['route'], ['tenant' => $case['model'][0], 'document' => $case['model'][1]])
                : route($case['route'], $case['model']);

            $this->assertStringContainsString($case['token'], $url, "Route {$case['route']} missing public token");
            $this->assertValidPublicIdToken($case['token']);
            $this->assertDoesNotMatchRegularExpression(
                '#/'.$case['numeric'].'(/|\?|$)#',
                $url,
                "Route {$case['route']} contains raw numeric segment",
            );
        }
    }

    private function assertPublicIdNotUsedAsVisibleLabel(string $html, string $publicId): void
    {
        $stripped = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $stripped = preg_replace('/\b(:href|href|src|action|data-[a-z-]+)=("|\')[^"\']*("|\')/i', '', $stripped) ?? $stripped;

        $this->assertStringNotContainsString('>'.$publicId.'<', $stripped);
        $this->assertStringNotContainsString('>'.$publicId.'</', $stripped);
    }

    private function makeFixtures(): void
    {
        $this->makeTenant();
        $this->makeInvoice('AUD-001');
        Server::query()->create(['name' => 'Audit Node', 'provider' => 'manual', 'status' => 'active']);
    }

    private function makeTenant(): Tenant
    {
        $project = HostedProject::query()->create([
            'name' => 'Audit App',
            'domain' => 'audit.test',
        ]);

        return Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Audit Tenant Ltd',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
    }

    private function makeInvoice(string $number): TenantInvoice
    {
        $tenant = $this->makeTenant();

        return TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => $number,
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'total' => 1500,
            'amount_due' => 1500,
            'status' => 'draft',
        ]);
    }

    private function assertValidPublicIdToken(string $publicId): void
    {
        $this->assertMatchesRegularExpression(PublicIdFormat::PATTERN, $publicId);
    }
}
