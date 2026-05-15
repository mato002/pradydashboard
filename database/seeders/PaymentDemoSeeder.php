<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (TenantPayment::query()->exists()) {
            return;
        }

        $tenants = Tenant::query()->get();
        if ($tenants->isEmpty()) {
            (new SubscriptionDemoSeeder)->run();
            $tenants = Tenant::query()->get();
        }

        if ($tenants->isEmpty()) {
            return;
        }

        $gateways = ['mpesa', 'stripe', 'paypal', 'flutterwave', 'bank_transfer'];
        $methods = [
            'mpesa' => 'M-Pesa',
            'stripe' => 'Card',
            'paypal' => 'PayPal',
            'flutterwave' => 'Card / Mobile',
            'bank_transfer' => 'Bank Transfer',
        ];
        $statuses = [
            'successful' => 58,
            'pending' => 12,
            'failed' => 10,
            'refunded' => 6,
            'reversed' => 4,
        ];

        $statusPool = [];
        foreach ($statuses as $status => $weight) {
            $statusPool = array_merge($statusPool, array_fill(0, $weight, $status));
        }

        $amounts = [4999, 7499, 9999, 14999, 19999, 24999, 34999, 49999, 89999, 125000];

        foreach (range(1, 48) as $i) {
            $tenant = $tenants->get($i % $tenants->count());
            $gateway = $gateways[$i % count($gateways)];
            $status = $statusPool[$i % count($statusPool)];
            $amount = $amounts[$i % count($amounts)] * (0.85 + (($i % 5) * 0.05));

            $invoice = TenantInvoice::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'invoice_number' => 'INV-'.str_pad((string) ($tenant->id * 100 + $i), 6, '0', STR_PAD_LEFT),
                ],
                [
                    'amount_due' => $amount,
                    'amount_paid' => $status === 'successful' ? $amount : 0,
                    'due_date' => now()->subDays(rand(1, 45))->toDateString(),
                    'status' => match ($status) {
                        'successful' => 'paid',
                        'pending' => 'sent',
                        default => 'overdue',
                    },
                ]
            );

            $paidAt = match ($status) {
                'pending' => null,
                'failed' => now()->subHours(rand(1, 72)),
                default => now()->subDays(rand(0, 90))->subHours(rand(0, 23)),
            };

            TenantPayment::query()->create([
                'transaction_id' => 'TXN-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'tenant_id' => $tenant->id,
                'tenant_invoice_id' => $invoice->id,
                'amount' => $amount,
                'currency' => 'KES',
                'status' => $status,
                'paid_at' => $paidAt,
                'method' => $methods[$gateway],
                'gateway' => $gateway,
                'reference' => strtoupper(substr($gateway, 0, 3)).'-'.Str::upper(Str::random(10)),
            ]);
        }
    }
}
