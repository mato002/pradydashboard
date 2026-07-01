<?php

namespace Database\Seeders;

use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\InvoicePaymentRecorder;
use App\Domain\Billing\StatementGenerator;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\BillingAutomationRule;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Safe local demo data for financial operations and document testing.
 * Idempotent — uses updateOrCreate; does NOT run migrate:fresh.
 */
class LocalFinancialTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacBootstrapSeeder::class,
            DocumentTemplateSeeder::class,
            PradyClassicA5TemplateSeeder::class,
            LicenseModuleSeeder::class,
        ]);

        $this->seedPlatformBillingSettings();

        BillingAutomationRule::platform()->update([
            'auto_generate_pdf' => true,
            'auto_send_invoices' => false,
            'auto_send_receipts' => false,
        ]);

        $pageCapital = $this->upsertTenant([
            'tenant_key' => 'page-capital',
            'company_name' => 'PAGE CAPITAL LTD',
            'product_name' => 'Prady MFI System',
            'product_slug' => 'mfi',
            'project_domain' => 'page-capital.mfi.test',
            'email' => 'edudechumba6767@gmail.com',
            'billing_email' => 'edudechumba6767@gmail.com',
            'primary_contact_email' => 'edudechumba6767@gmail.com',
            'billing_contact_name' => 'PAGE CAPITAL LTD',
            'contact_person' => 'PAGE CAPITAL LTD',
            'billing_phone' => '0722 295 194',
            'phone' => '0722 295 194',
            'billing_address' => 'Nakuru, Barnabas, Opp. Epic Academy',
            'physical_address' => 'Nakuru, Barnabas, Opp. Epic Academy',
            'county_city' => 'Nakuru',
            'subscription_plan' => 'Professional',
            'subscription_amount' => 30000,
            'industry' => 'mfi',
            'business_type' => 'Microfinance',
        ]);

        $joshTenant = $this->upsertTenant([
            'tenant_key' => 'josh-atkiprono',
            'company_name' => 'Josh At Kiprono Ltd',
            'product_name' => 'Property Management System',
            'product_slug' => 'property',
            'project_domain' => 'josh-atkiprono.property.test',
            'email' => 'joshatkiprono@gmail.com',
            'billing_email' => 'joshatkiprono@gmail.com',
            'primary_contact_email' => 'joshatkiprono@gmail.com',
            'billing_contact_name' => 'Josh At Kiprono',
            'contact_person' => 'Josh At Kiprono',
            'billing_phone' => '0110 098 548',
            'phone' => '0110 098 548',
            'billing_address' => 'Nairobi, Kenya',
            'physical_address' => 'Nairobi, Kenya',
            'county_city' => 'Nairobi',
            'subscription_plan' => 'Standard',
            'subscription_amount' => 15000,
            'industry' => 'property',
            'business_type' => 'Real Estate',
        ]);

        $this->seedFinancialDocuments($pageCapital, 'PAGE');
        $this->seedFinancialDocuments($joshTenant, 'JOSH');

        $this->command?->info('Local financial test data seeded.');
        $this->command?->info('Tenants: PAGE CAPITAL LTD (edudechumba6767@gmail.com), Josh At Kiprono Ltd (joshatkiprono@gmail.com)');
    }

    private function seedPlatformBillingSettings(): void
    {
        Setting::setJson('platform.billing', array_merge(
            Setting::getJson('platform.billing') ?? [],
            [
                'company_legal_name' => config('billing.company_legal_name'),
                'trading_name' => config('billing.trading_name'),
                'tax_pin' => config('billing.tax_pin'),
                'issuer_phone' => config('billing.issuer_phone'),
                'issuer_email' => config('billing.issuer_email'),
                'issuer_website' => config('billing.issuer_website'),
                'issuer_address' => config('billing.issuer_address'),
                'issuer_tagline' => config('billing.issuer_tagline'),
                'numbering_style' => config('billing.numbering_style', 'short'),
                'bank_name' => config('billing.bank_name'),
                'bank_account_name' => config('billing.bank_account_name'),
                'bank_account_number' => config('billing.bank_account_number'),
                'bank_branch' => config('billing.bank_branch'),
                'mpesa_paybill' => config('billing.mpesa_paybill'),
                'paybill_account_number' => config('billing.paybill_account_number'),
                'default_currency' => config('billing.default_currency', 'KES'),
            ],
        ));
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function upsertTenant(array $definition): Tenant
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => $definition['product_slug']],
            [
                'name' => $definition['product_name'],
                'description' => $definition['product_name'].' — PradytecAI',
                'category' => 'saas',
                'status' => 'active',
                'default_billing_model' => 'subscription',
                'default_license_mode' => 'module',
            ],
        );

        $project = Project::query()->updateOrCreate(
            ['domain' => $definition['project_domain']],
            [
                'product_id' => $product->id,
                'name' => $definition['company_name'].' — '.$definition['product_name'],
                'base_url' => 'https://'.$definition['project_domain'],
                'environment' => 'demo',
                'product_key' => $product->slug,
                'status' => 'active',
            ],
        );

        $tenant = Tenant::query()->updateOrCreate(
            ['tenant_key' => $definition['tenant_key']],
            [
                'hosted_project_id' => $project->id,
                'product_id' => $product->id,
                'company_name' => $definition['company_name'],
                'business_type' => $definition['business_type'],
                'industry' => $definition['industry'],
                'status' => 'active',
                'subscription_plan' => $definition['subscription_plan'],
                'subscription_amount' => $definition['subscription_amount'],
                'billing_cycle' => 'monthly',
                'tenant_currency' => 'KES',
                'billing_preferred_currency' => 'KES',
                'email' => $definition['email'],
                'billing_email' => $definition['billing_email'],
                'primary_contact_email' => $definition['primary_contact_email'],
                'billing_contact_name' => $definition['billing_contact_name'],
                'contact_person' => $definition['contact_person'],
                'phone' => $definition['phone'],
                'billing_phone' => $definition['billing_phone'],
                'billing_address' => $definition['billing_address'],
                'physical_address' => $definition['physical_address'],
                'county_city' => $definition['county_city'],
                'country' => 'KE',
                'tenant_domain' => $definition['project_domain'],
                'start_date' => now()->subMonths(4)->toDateString(),
                'renewal_date' => now()->addDays(20)->toDateString(),
                'grace_days' => 7,
            ],
        );

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant->fresh('project'));

        return $tenant->fresh(['project']);
    }

    private function seedFinancialDocuments(Tenant $tenant, string $prefix): void
    {
        $subscription = TenantProjectSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->first();

        $lineDescription = 'Prady Technologies Microfinance Renting';
        $amount = 30000.0;

        $openInvoice = $this->upsertBillableDocument(
            tenant: $tenant,
            subscription: $subscription,
            invoiceNumber: "{$prefix}-INV-OPEN",
            documentType: BillingDocumentType::INVOICE,
            status: 'sent',
            amount: $amount,
            paid: 0,
            lineDescription: $lineDescription,
            issueDate: now()->subDays(10),
            dueDate: now()->addDays(14),
            finalize: true,
        );

        $this->upsertBillableDocument(
            tenant: $tenant,
            subscription: $subscription,
            invoiceNumber: "{$prefix}-PRO-001",
            documentType: BillingDocumentType::PROFORMA,
            status: 'draft',
            amount: $amount,
            paid: 0,
            lineDescription: $lineDescription,
            issueDate: now()->subDays(3),
            dueDate: now()->addDays(30),
            finalize: true,
        );

        $this->upsertBillableDocument(
            tenant: $tenant,
            subscription: $subscription,
            invoiceNumber: "{$prefix}-QUO-001",
            documentType: BillingDocumentType::QUOTATION,
            status: 'draft',
            amount: $amount,
            paid: 0,
            lineDescription: $lineDescription,
            issueDate: now()->subDays(5),
            dueDate: now()->addDays(21),
            finalize: true,
            approvalStatus: 'pending',
        );

        $paidInvoice = $this->upsertBillableDocument(
            tenant: $tenant,
            subscription: $subscription,
            invoiceNumber: "{$prefix}-INV-PAID",
            documentType: BillingDocumentType::INVOICE,
            status: 'paid',
            amount: $amount,
            paid: $amount,
            lineDescription: $lineDescription,
            issueDate: now()->subDays(45),
            dueDate: now()->subDays(15),
            finalize: true,
        );

        $this->ensurePaymentAndReceipt($paidInvoice, $amount);

        $this->upsertBillableDocument(
            tenant: $tenant,
            subscription: $subscription,
            invoiceNumber: "{$prefix}-INV-PARTIAL",
            documentType: BillingDocumentType::INVOICE,
            status: 'partial',
            amount: $amount,
            paid: 12000,
            lineDescription: $lineDescription,
            issueDate: now()->subDays(20),
            dueDate: now()->subDays(2),
            finalize: true,
        );

        TenantPayment::query()->updateOrCreate(
            ['transaction_id' => "{$prefix}-PAY-PARTIAL"],
            [
                'tenant_id' => $tenant->id,
                'tenant_invoice_id' => TenantInvoice::query()
                    ->where('invoice_number', "{$prefix}-INV-PARTIAL")
                    ->value('id'),
                'amount' => 12000,
                'currency' => 'KES',
                'status' => 'successful',
                'gateway' => 'mpesa',
                'method' => 'M-Pesa STK',
                'reference' => "{$prefix}-MPX-001",
                'paid_at' => now()->subDays(4),
                'source' => 'manual',
            ],
        );

        if (! TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', BillingDocumentType::STATEMENT)
            ->where('statement_period_start', now()->startOfMonth()->toDateString())
            ->exists()
        ) {
            app(StatementGenerator::class)->generate(
                $tenant,
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            );
        }

        unset($openInvoice);
    }

    private function upsertBillableDocument(
        Tenant $tenant,
        ?TenantProjectSubscription $subscription,
        string $invoiceNumber,
        string $documentType,
        string $status,
        float $amount,
        float $paid,
        string $lineDescription,
        Carbon $issueDate,
        Carbon $dueDate,
        bool $finalize,
        ?string $approvalStatus = null,
    ): TenantInvoice {
        $balance = max(0, round($amount - $paid, 2));

        $invoice = TenantInvoice::query()->updateOrCreate(
            ['invoice_number' => $invoiceNumber],
            [
                'tenant_id' => $tenant->id,
                'tenant_project_subscription_id' => $subscription?->id,
                'document_type' => $documentType,
                'currency' => 'KES',
                'subtotal' => $amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $amount,
                'amount_due' => $balance,
                'amount_paid' => $paid,
                'status' => $status,
                'approval_status' => $approvalStatus,
                'issue_date' => $issueDate->toDateString(),
                'issued_at' => $issueDate,
                'due_date' => $dueDate->toDateString(),
                'product_name' => $tenant->project?->name ?? 'Prady Platform',
                'generated_by' => 'LocalFinancialTestDataSeeder',
                'created_source' => 'manual',
                'delivery_status' => 'not_sent',
            ],
        );

        TenantInvoiceLineItem::query()->updateOrCreate(
            [
                'tenant_invoice_id' => $invoice->id,
                'description' => $lineDescription,
            ],
            [
                'item_type' => $documentType === BillingDocumentType::RECEIPT ? 'payment' : 'subscription',
                'quantity' => 1,
                'unit_price' => $amount,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'line_total' => $amount,
            ],
        );

        if ($finalize && ! $invoice->finalized_at) {
            app(DocumentFinalizer::class)->finalize(
                $invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']),
            );
        }

        return $invoice->fresh(['lineItems', 'tenant', 'payments']);
    }

    private function ensurePaymentAndReceipt(TenantInvoice $invoice, float $amount): void
    {
        $reference = $invoice->invoice_number.'-PAY';

        $payment = TenantPayment::query()->updateOrCreate(
            ['reference' => $reference],
            [
                'tenant_id' => $invoice->tenant_id,
                'tenant_invoice_id' => $invoice->id,
                'amount' => $amount,
                'currency' => 'KES',
                'status' => 'successful',
                'gateway' => 'mpesa',
                'method' => 'M-Pesa Paybill',
                'transaction_id' => str_replace('-', '', $reference),
                'paid_at' => $invoice->issue_date ?? now()->subDays(30),
                'source' => 'manual',
            ],
        );

        $invoice->update([
            'amount_paid' => $amount,
            'amount_due' => 0,
            'status' => 'paid',
        ]);

        if (! TenantInvoice::query()
            ->where('document_type', BillingDocumentType::RECEIPT)
            ->where('linked_invoice_id', $invoice->id)
            ->exists()
        ) {
            app(InvoicePaymentRecorder::class)->record($invoice->fresh(), [
                'amount' => $amount,
                'payment_date' => $payment->paid_at ?? now()->subDays(30),
                'method' => 'M-Pesa Paybill',
                'reference' => $reference,
            ]);
        }
    }
}
