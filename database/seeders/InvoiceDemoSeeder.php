<?php

namespace Database\Seeders;

use App\Models\InvoiceRecurringSchedule;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (TenantInvoice::query()->exists()) {
            return;
        }

        if (Tenant::query()->doesntExist()) {
            $this->bootstrapTenants();
        }

        $tenants = Tenant::query()->with('project')->get();
        if ($tenants->isEmpty()) {
            return;
        }

        $this->seedInvoices($tenants);
        $this->seedRecurringSchedules($tenants);
    }

    private function seedInvoices($tenants): void
    {
        $templates = [
            ['status' => 'paid', 'paid_ratio' => 1, 'days_offset' => -12, 'method' => 'M-Pesa STK', 'recurring' => false, 'failed' => false],
            ['status' => 'paid', 'paid_ratio' => 1, 'days_offset' => -28, 'method' => 'Bank transfer', 'recurring' => true, 'failed' => false],
            ['status' => 'pending', 'paid_ratio' => 0, 'days_offset' => 14, 'method' => null, 'recurring' => false, 'failed' => false],
            ['status' => 'overdue', 'paid_ratio' => 0, 'days_offset' => -9, 'method' => null, 'recurring' => true, 'failed' => false],
            ['status' => 'partial', 'paid_ratio' => 0.45, 'days_offset' => -3, 'method' => 'Card (Stripe)', 'recurring' => false, 'failed' => false],
            ['status' => 'cancelled', 'paid_ratio' => 0, 'days_offset' => 30, 'method' => null, 'recurring' => false, 'failed' => false],
            ['status' => 'paid', 'paid_ratio' => 1, 'days_offset' => -45, 'method' => 'M-Pesa Paybill', 'recurring' => true, 'failed' => false],
            ['status' => 'overdue', 'paid_ratio' => 0, 'days_offset' => -21, 'method' => null, 'recurring' => false, 'failed' => true],
            ['status' => 'pending', 'paid_ratio' => 0, 'days_offset' => 7, 'method' => null, 'recurring' => true, 'failed' => false],
            ['status' => 'partial', 'paid_ratio' => 0.62, 'days_offset' => -5, 'method' => 'Bank transfer', 'recurring' => false, 'failed' => false],
            ['status' => 'paid', 'paid_ratio' => 1, 'days_offset' => -2, 'method' => 'M-Pesa STK', 'recurring' => false, 'failed' => false],
            ['status' => 'overdue', 'paid_ratio' => 0, 'days_offset' => -14, 'method' => null, 'recurring' => true, 'failed' => true],
            ['status' => 'paid', 'paid_ratio' => 1, 'days_offset' => -60, 'method' => 'Card (Stripe)', 'recurring' => true, 'failed' => false],
            ['status' => 'pending', 'paid_ratio' => 0, 'days_offset' => 21, 'method' => null, 'recurring' => false, 'failed' => false],
        ];

        $generators = ['Billing automation', 'Finance ops', 'Subscription sync', 'Manual — Admin', 'Tax engine'];

        foreach ($templates as $i => $tpl) {
            $tenant = $tenants->get($i % $tenants->count());
            $base = (float) ($tenant->subscription_amount ?: 14999);
            $tax = round($base * 0.16, 2);
            $due = (float) $base + $tax;
            $paid = round($due * $tpl['paid_ratio'], 2);
            $issued = now()->subDays(abs($tpl['days_offset']) + random_int(2, 18));

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $tenant->id,
                'invoice_number' => 'INV-'.now()->format('Y').'-'.str_pad((string) (1000 + $i), 5, '0', STR_PAD_LEFT),
                'product_name' => $tenant->project?->name ?? 'Prady Platform License',
                'amount_due' => $due,
                'amount_paid' => $paid,
                'penalty_amount' => $tpl['status'] === 'overdue' ? round($due * 0.02, 2) : 0,
                'tax_amount' => $tax,
                'due_date' => now()->addDays($tpl['days_offset']),
                'issued_at' => $issued,
                'status' => $tpl['status'],
                'payment_method' => $tpl['method'],
                'generated_by' => $generators[$i % count($generators)],
                'is_recurring' => $tpl['recurring'],
                'pdf_generated' => ! in_array($tpl['status'], ['cancelled'], true),
                'email_delivered_at' => $tpl['status'] !== 'cancelled' ? $issued->copy()->addHours(2) : null,
                'collection_failed' => $tpl['failed'],
            ]);

            if ($paid > 0) {
                TenantPayment::query()->create([
                    'tenant_id' => $tenant->id,
                    'tenant_invoice_id' => $invoice->id,
                    'amount' => $paid,
                    'paid_at' => $issued->copy()->addDays(random_int(1, 8)),
                    'method' => $tpl['method'] ?? 'Manual allocation',
                    'reference' => 'PAY-'.strtoupper(substr(md5((string) $invoice->id), 0, 10)),
                ]);
            }
        }
    }

    private function bootstrapTenants(): void
    {
        (new ServerHealthDemoSeeder)->run();

        $server = Server::query()->first();
        $project = Project::query()->first();

        if (! $project && $server) {
            $project = Project::query()->create([
                'server_id' => $server->id,
                'name' => 'Prady Core Platform',
                'domain' => 'core.prady.local',
                'status' => 'active',
                'api_token' => Str::random(64),
            ]);
        }

        if (! $project) {
            return;
        }

        $this->call(SubscriptionDemoSeeder::class);
    }

    private function seedRecurringSchedules($tenants): void
    {
        if (InvoiceRecurringSchedule::query()->exists()) {
            return;
        }

        $schedules = [
            ['name' => 'Monthly platform license', 'freq' => 'monthly', 'tax' => 16, 'offset_days' => 3],
            ['name' => 'Quarterly support retainer', 'freq' => 'quarterly', 'tax' => 16, 'offset_days' => 12],
            ['name' => 'Annual enterprise bundle', 'freq' => 'annual', 'tax' => 16, 'offset_days' => 45],
            ['name' => 'Add-on API overage', 'freq' => 'monthly', 'tax' => 16, 'offset_days' => 1],
            ['name' => 'White-label hosting', 'freq' => 'monthly', 'tax' => 16, 'offset_days' => 7],
            ['name' => 'Compliance archive fee', 'freq' => 'quarterly', 'tax' => 16, 'offset_days' => 20],
        ];

        foreach ($schedules as $i => $sch) {
            $tenant = $tenants->get($i % $tenants->count());
            $amount = (float) ($tenant->subscription_amount ?: 9999) * (0.5 + ($i * 0.15));

            InvoiceRecurringSchedule::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $sch['name'],
                'product_name' => $tenant->project?->name ?? 'Prady Platform',
                'amount' => round($amount, 2),
                'tax_rate' => $sch['tax'],
                'frequency' => $sch['freq'],
                'next_run_at' => now()->addDays($sch['offset_days']),
                'auto_email' => true,
                'auto_pdf' => true,
                'enabled' => $i !== 4,
                'generated_by' => 'Billing automation',
            ]);
        }
    }
}
