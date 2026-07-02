<?php

namespace App\Domain\Billing;

use App\Domain\Licensing\TenantLicenseBillingContext;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PublicBillingCheckoutService
{
    public function __construct(
        private readonly PaymentsGatewayClient $gatewayClient,
        private readonly TenantLicenseBillingContext $billingContext,
    ) {}

    /**
     * @return array{ok: bool, message: string, checkout_request_id?: string, transaction_uuid?: string}
     */
    public function initiateStkPush(Tenant $tenant, string $phone, ?TenantInvoice $invoice = null): array
    {
        if (! $this->gatewayClient->isConfigured()) {
            throw ValidationException::withMessages([
                'phone' => [__('Online payment is not configured yet. Use the bank/M-Pesa instructions below.')],
            ]);
        }

        if (! filled($tenant->payments_gateway_tenant_uuid)) {
            throw ValidationException::withMessages([
                'phone' => [__('This tenant is not linked to the payment gateway yet. Contact billing support.')],
            ]);
        }

        $billing = $this->billingContext->forTenant($tenant);
        $amount = (float) ($invoice?->balanceDue() ?? $billing['amount_due'] ?? 0);

        if ($amount <= 0.009) {
            throw ValidationException::withMessages([
                'phone' => [__('No outstanding balance to collect.')],
            ]);
        }

        $normalizedPhone = $this->normalizePhone($phone);

        $payload = [
            'tenant_uuid' => $tenant->payments_gateway_tenant_uuid,
            'phone_number' => $normalizedPhone,
            'amount' => round($amount, 2),
            'currency' => $invoice?->currency ?? $billing['currency'] ?? 'KES',
            'account_reference' => $invoice?->invoice_number ?? $billing['invoice_number'] ?? ('TENANT-'.$tenant->id),
            'transaction_desc' => __('Subscription payment for :company', ['company' => $tenant->company_name]),
            'callback_metadata' => [
                'dashboard_tenant_id' => $tenant->id,
                'invoice_id' => $invoice?->id,
                'invoice_number' => $invoice?->invoice_number,
            ],
        ];

        $response = $this->gatewayClient->initiateStkCollection($payload);

        if (! ($response['ok'] ?? false)) {
            throw ValidationException::withMessages([
                'phone' => [$response['message'] ?? $response['error'] ?? __('Could not start M-Pesa payment. Try again or pay manually.')],
            ]);
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => __('M-Pesa prompt sent. Approve on your phone to complete payment.'),
            'checkout_request_id' => Arr::get($data, 'checkout_request_id') ?? Arr::get($data, 'CheckoutRequestID'),
            'transaction_uuid' => Arr::get($data, 'uuid') ?? Arr::get($data, 'transaction.uuid'),
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '254'.substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            $digits = '254'.$digits;
        }

        if (! str_starts_with($digits, '254') || strlen($digits) < 12) {
            throw ValidationException::withMessages([
                'phone' => [__('Enter a valid Kenyan mobile number (e.g. 0712 345 678).')],
            ]);
        }

        return $digits;
    }
}
