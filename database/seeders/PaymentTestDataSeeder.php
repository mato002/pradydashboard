<?php

namespace Database\Seeders;

use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Database\Seeder;

class PaymentTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $projects = $this->seedProducts();
        $tenants = $this->seedTenants($projects);

        $provisioner = new TenantProjectProvisioner;

        foreach ($tenants as $tenant) {
            $provisioner->syncPrimarySubscription($tenant->fresh('project'));
        }

        $this->seedPaymentFixtures($tenants);
    }

    /**
     * @return array<string, Project>
     */
    private function seedProducts(): array
    {
        $definitions = [
            'mfi' => [
                'name' => 'MFI SaaS',
                'domain' => 'mfi.pradytecai.test',
                'description' => 'Microfinance and SACCO core banking platform',
                'default_monthly_fee' => 24999,
                'default_setup_fee' => 50000,
            ],
            'property' => [
                'name' => 'Property SaaS',
                'domain' => 'property.pradytecai.test',
                'description' => 'Property management and rent collection',
                'default_monthly_fee' => 14999,
                'default_setup_fee' => 25000,
            ],
            'erp' => [
                'name' => 'ERP Dashboard',
                'domain' => 'erp.pradytecai.test',
                'description' => 'Business operations and inventory ERP',
                'default_monthly_fee' => 9999,
                'default_setup_fee' => 15000,
            ],
        ];

        $projects = [];

        foreach ($definitions as $slug => $definition) {
            $projects[$slug] = Project::query()->updateOrCreate(
                ['product_slug' => $slug],
                array_merge($definition, [
                    'product_key' => $slug,
                    'status' => 'active',
                    'currency' => 'KES',
                    'license_validation_mode' => 'api',
                    'grace_period_days' => 7,
                    'billing_model' => 'subscription',
                    'trial_days' => 14,
                ])
            );
        }

        return $projects;
    }

    /**
     * @param  array<string, Project>  $projects
     * @return list<Tenant>
     */
    private function seedTenants(array $projects): array
    {
        $definitions = [
            [
                'tenant_key' => 'acme-mfi',
                'company_name' => 'Acme Microfinance Ltd',
                'project_slug' => 'mfi',
                'tenant_domain' => 'acme-mfi.test',
                'industry' => 'mfi',
                'business_type' => 'Microfinance',
                'status' => 'active',
                'subscription_plan' => 'Professional',
                'subscription_amount' => 24999,
                'billing_cycle' => 'monthly',
                'tenant_currency' => 'KES',
                'email' => 'ops@acme-mfi.test',
                'billing_email' => 'billing@acme-mfi.test',
                'billing_phone' => '+254712000101',
                'phone' => '+254712000101',
                'contact_person' => 'Jane Wanjiku',
                'county_city' => 'Nairobi',
                'country' => 'KE',
            ],
            [
                'tenant_key' => 'summit-sacco',
                'company_name' => 'Summit SACCO',
                'project_slug' => 'mfi',
                'tenant_domain' => 'summit-sacco.test',
                'industry' => 'mfi',
                'business_type' => 'SACCO',
                'status' => 'active',
                'subscription_plan' => 'Enterprise',
                'subscription_amount' => 34999,
                'billing_cycle' => 'monthly',
                'tenant_currency' => 'KES',
                'email' => 'admin@summit-sacco.test',
                'billing_email' => 'finance@summit-sacco.test',
                'billing_phone' => '+254712000202',
                'phone' => '+254712000202',
                'contact_person' => 'Peter Otieno',
                'county_city' => 'Kisumu',
                'country' => 'KE',
            ],
            [
                'tenant_key' => 'beta-properties',
                'company_name' => 'Beta Properties Ltd',
                'project_slug' => 'property',
                'tenant_domain' => 'beta-properties.test',
                'industry' => 'property',
                'business_type' => 'Real Estate',
                'status' => 'active',
                'subscription_plan' => 'Standard',
                'subscription_amount' => 14999,
                'billing_cycle' => 'monthly',
                'tenant_currency' => 'KES',
                'email' => 'hello@beta-properties.test',
                'billing_email' => 'accounts@beta-properties.test',
                'billing_phone' => '+254712000303',
                'phone' => '+254712000303',
                'contact_person' => 'Grace Mwangi',
                'county_city' => 'Mombasa',
                'country' => 'KE',
            ],
        ];

        $tenants = [];

        foreach ($definitions as $definition) {
            $projectSlug = $definition['project_slug'];
            unset($definition['project_slug']);

            $tenants[] = Tenant::query()->updateOrCreate(
                ['tenant_key' => $definition['tenant_key']],
                array_merge($definition, [
                    'project_id' => $projects[$projectSlug]->id,
                    'start_date' => now()->subMonths(3)->toDateString(),
                    'renewal_date' => now()->addDays(18)->toDateString(),
                    'grace_days' => 7,
                ])
            );
        }

        return $tenants;
    }

    /**
     * @param  list<Tenant>  $tenants
     */
    private function seedPaymentFixtures(array $tenants): void
    {
        $byKey = collect($tenants)->keyBy('tenant_key');

        $acme = $byKey->get('acme-mfi');
        $summit = $byKey->get('summit-sacco');
        $beta = $byKey->get('beta-properties');

        if ($acme) {
            $this->seedInvoiceWithOptionalPayment(
                tenant: $acme,
                invoiceNumber: 'INV-MFI-ACME-001',
                amountDue: 28998.84,
                taxAmount: 3998.84,
                status: 'pending',
                dueDaysOffset: 7,
                payment: null,
            );
        }

        if ($summit) {
            $this->seedInvoiceWithOptionalPayment(
                tenant: $summit,
                invoiceNumber: 'INV-MFI-SUMMIT-001',
                amountDue: 40599.84,
                taxAmount: 5599.84,
                status: 'paid',
                dueDaysOffset: -14,
                payment: [
                    'amount' => 40599.84,
                    'status' => 'successful',
                    'gateway' => 'mpesa',
                    'method' => 'M-Pesa Paybill',
                    'reference' => 'MPX-SUMMIT-001',
                    'transaction_id' => 'TXN-MFI-SUMMIT-001',
                    'paid_days_ago' => 10,
                ],
            );

            $this->seedInvoiceWithOptionalPayment(
                tenant: $summit,
                invoiceNumber: 'INV-MFI-SUMMIT-002',
                amountDue: 40599.84,
                taxAmount: 5599.84,
                status: 'partial',
                dueDaysOffset: -3,
                payment: [
                    'amount' => 20000,
                    'status' => 'successful',
                    'gateway' => 'mpesa',
                    'method' => 'M-Pesa STK',
                    'reference' => 'MPX-SUMMIT-002',
                    'transaction_id' => 'TXN-MFI-SUMMIT-002',
                    'paid_days_ago' => 2,
                ],
            );
        }

        if ($beta) {
            $this->seedInvoiceWithOptionalPayment(
                tenant: $beta,
                invoiceNumber: 'INV-PROP-BETA-001',
                amountDue: 17399.84,
                taxAmount: 2399.84,
                status: 'overdue',
                dueDaysOffset: -9,
                payment: null,
            );
        }
    }

    /**
     * @param  array<string, mixed>|null  $payment
     */
    private function seedInvoiceWithOptionalPayment(
        Tenant $tenant,
        string $invoiceNumber,
        float $amountDue,
        float $taxAmount,
        string $status,
        int $dueDaysOffset,
        ?array $payment,
    ): void {
        $amountPaid = (float) ($payment['amount'] ?? 0);

        $invoice = TenantInvoice::query()->updateOrCreate(
            ['invoice_number' => $invoiceNumber],
            [
                'tenant_id' => $tenant->id,
                'product_name' => $tenant->project?->name ?? 'Prady Platform',
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'tax_amount' => $taxAmount,
                'due_date' => now()->addDays($dueDaysOffset),
                'issued_at' => now()->subDays(abs($dueDaysOffset) + 5),
                'status' => $status,
                'payment_method' => $payment['method'] ?? null,
                'generated_by' => 'Payment test seed',
                'is_recurring' => true,
                'pdf_generated' => true,
            ]
        );

        if ($payment === null) {
            return;
        }

        TenantPayment::query()->updateOrCreate(
            ['transaction_id' => $payment['transaction_id']],
            [
                'tenant_id' => $tenant->id,
                'tenant_invoice_id' => $invoice->id,
                'amount' => $payment['amount'],
                'currency' => 'KES',
                'status' => $payment['status'],
                'gateway' => $payment['gateway'],
                'method' => $payment['method'],
                'reference' => $payment['reference'],
                'paid_at' => now()->subDays($payment['paid_days_ago'])->subHours(3),
                'source' => 'manual',
            ]
        );
    }
}
