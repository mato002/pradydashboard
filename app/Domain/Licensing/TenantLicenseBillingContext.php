<?php

namespace App\Domain\Licensing;

use App\Domain\Billing\BillingSettings;
use App\Domain\Billing\BillingSummary;
use App\Domain\Settings\PlatformSettingsService;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class TenantLicenseBillingContext
{
    /**
     * Payment details for hosted products when access is restricted.
     *
     * @return array<string, mixed>|null
     */
    public function forTenant(Tenant $tenant): ?array
    {
        $tenant->loadMissing('latestAccessControl');

        $openInvoices = $this->openInvoices($tenant);

        $overdue = $openInvoices->filter(
            fn (TenantInvoice $inv) => $inv->status === 'overdue'
                || ($inv->due_date && $inv->due_date->isPast() && $inv->balanceDue() > 0)
        );

        $primary = $overdue->first() ?? $openInvoices->first();
        $amountDue = $this->resolveAmountDue($tenant, $openInvoices);

        $billingBlocked = in_array($tenant->status, ['overdue', 'suspended', 'restricted'], true)
            || $tenant->latestAccessControl?->level === 'restricted';

        if ($amountDue <= 0 && ! $billingBlocked) {
            return null;
        }

        $currency = $primary?->currency ?? $tenant->tenant_currency ?? (new BillingSettings)->defaultCurrency();
        $billing = (new BillingSettings)->all();
        $company = (new PlatformSettingsService)->all()['company'] ?? [];

        $billingEmail = $tenant->billing_email ?: ($company['billing_email'] ?? null);
        $billingPhone = $tenant->billing_phone ?: ($company['phone'] ?? null);
        $supportEmail = $company['support_email'] ?? $billingEmail;
        $instructions = trim((string) ($billing['payment_instructions'] ?? ''));

        if ($instructions === '') {
            $instructions = $this->defaultPaymentInstructions(
                $amountDue,
                $currency,
                $billing['company_legal_name'] ?? null,
                $primary?->invoice_number,
                $tenant->company_name,
            );
        }

        $paymentUrl = URL::temporarySignedRoute('billing.pay', now()->addDays(30), ['tenant' => $tenant->id]);

        $actions = [
            [
                'type' => 'pay',
                'label' => __('Pay now'),
                'href' => $paymentUrl,
                'primary' => true,
            ],
        ];

        if (filled($billingPhone)) {
            $actions[] = [
                'type' => 'phone',
                'label' => __('Call billing'),
                'href' => 'tel:'.preg_replace('/\s+/', '', $billingPhone),
            ];
        }

        if (filled($billingEmail)) {
            $subject = rawurlencode(__('Payment for :company', ['company' => $tenant->company_name]));
            $body = rawurlencode(__('Invoice :number — please confirm payment instructions.', [
                'number' => $primary?->invoice_number ?? '—',
            ]));
            $actions[] = [
                'type' => 'email',
                'label' => __('Email billing'),
                'href' => 'mailto:'.$billingEmail.'?subject='.$subject.'&body='.$body,
            ];
        }

        if (filled($supportEmail) && $supportEmail !== $billingEmail) {
            $actions[] = [
                'type' => 'support',
                'label' => __('Contact support'),
                'href' => 'mailto:'.$supportEmail,
            ];
        }

        return [
            'company_name' => $tenant->company_name,
            'amount_due' => $amountDue,
            'amount_due_formatted' => TenantInvoice::formatMoney($amountDue, $currency),
            'currency' => $currency,
            'invoice_number' => $primary?->invoice_number,
            'due_date' => $primary?->due_date?->toFormattedDateString(),
            'overdue_invoice_count' => $overdue->count(),
            'payment_instructions' => $instructions,
            'billing_email' => $billingEmail,
            'billing_phone' => $billingPhone,
            'support_email' => $supportEmail,
            'payment_url' => $paymentUrl,
            'actions' => $actions,
        ];
    }

    /**
     * Minimal billing payload when invoices are missing but access is blocked.
     *
     * @return array<string, mixed>
     */
    public function fallbackForTenant(Tenant $tenant): array
    {
        $billing = (new BillingSettings)->all();
        $company = (new PlatformSettingsService)->all()['company'] ?? [];
        $openInvoices = $this->openInvoices($tenant);
        $primary = $openInvoices->first();
        $currency = $primary?->currency ?? $tenant->tenant_currency ?? (new BillingSettings)->defaultCurrency();
        $amountDue = $this->resolveAmountDue($tenant, $openInvoices);
        $billingEmail = $tenant->billing_email ?: ($company['billing_email'] ?? null);
        $billingPhone = $tenant->billing_phone ?: ($company['phone'] ?? null);
        $instructions = trim((string) ($billing['payment_instructions'] ?? ''));

        if ($instructions === '') {
            $instructions = $this->defaultPaymentInstructions(
                $amountDue,
                $currency,
                $billing['company_legal_name'] ?? null,
                $primary?->invoice_number,
                $tenant->company_name,
            );
        }

        $paymentUrl = URL::temporarySignedRoute('billing.pay', now()->addDays(30), ['tenant' => $tenant->id]);

        $actions = [
            [
                'type' => 'pay',
                'label' => __('Pay now'),
                'href' => $paymentUrl,
                'primary' => true,
            ],
        ];

        if (filled($billingPhone)) {
            $actions[] = [
                'type' => 'phone',
                'label' => __('Call billing'),
                'href' => 'tel:'.preg_replace('/\s+/', '', $billingPhone),
            ];
        }

        if (filled($billingEmail)) {
            $actions[] = [
                'type' => 'email',
                'label' => __('Email billing'),
                'href' => 'mailto:'.$billingEmail,
            ];
        }

        return [
            'company_name' => $tenant->company_name,
            'amount_due' => $amountDue,
            'amount_due_formatted' => TenantInvoice::formatMoney($amountDue, $currency),
            'currency' => $currency,
            'invoice_number' => $primary?->invoice_number,
            'due_date' => $primary?->due_date?->toFormattedDateString(),
            'payment_instructions' => $instructions,
            'billing_email' => $billingEmail,
            'billing_phone' => $billingPhone,
            'payment_url' => $paymentUrl,
            'actions' => $actions,
        ];
    }

    /** @return Collection<int, TenantInvoice> */
    private function openInvoices(Tenant $tenant): Collection
    {
        return TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotIn('status', ['cancelled', 'void', 'paid'])
            ->where('document_type', 'invoice')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * @param  Collection<int, TenantInvoice>  $openInvoices
     */
    private function resolveAmountDue(Tenant $tenant, Collection $openInvoices): float
    {
        $fromInvoices = round((float) $openInvoices->sum(fn (TenantInvoice $i) => $i->balanceDue()), 2);

        if ($fromInvoices > 0) {
            return $fromInvoices;
        }

        $summary = app(BillingSummary::class)->forTenant($tenant);
        if (($summary['outstanding'] ?? 0) > 0) {
            return round((float) $summary['outstanding'], 2);
        }

        $tenant->loadMissing('projectSubscriptions');

        $subscriptionFee = round((float) $tenant->projectSubscriptions
            ->filter(fn ($s) => ($s->product_status ?? 'active') === 'active')
            ->sum(fn ($s) => (float) ($s->monthly_fee ?? 0)), 2);

        if ($subscriptionFee > 0) {
            return $subscriptionFee;
        }

        if ((float) ($tenant->subscription_amount ?? 0) > 0) {
            return round((float) $tenant->subscription_amount, 2);
        }

        return 0.0;
    }

    private function defaultPaymentInstructions(
        float $amountDue,
        string $currency,
        ?string $companyLegalName,
        ?string $invoiceNumber = null,
        ?string $companyName = null,
    ): string {
        if ($amountDue > 0 && filled($companyLegalName)) {
            $message = __('Pay :amount to :company.', [
                'amount' => TenantInvoice::formatMoney($amountDue, $currency),
                'company' => $companyLegalName,
            ]);

            if (filled($invoiceNumber)) {
                $message .= ' '.__('Reference invoice :number on the payment.', ['number' => $invoiceNumber]);
            } elseif (filled($companyName)) {
                $message .= ' '.__('Reference your company name (:company) on the payment.', ['company' => $companyName]);
            }

            return $message;
        }

        if (filled($companyName)) {
            return __('Contact billing to complete your subscription payment. Reference your company name: :company.', [
                'company' => $companyName,
            ]);
        }

        return __('Contact billing to complete your subscription payment.');
    }
}
