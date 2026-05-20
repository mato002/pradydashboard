<?php

namespace App\Domain\Billing;

use App\Domain\Settings\PlatformSettingsService;
use App\Models\Setting;

class BillingSettings
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = (new PlatformSettingsService)->defaults('billing');
        $stored = Setting::getJson('platform.billing');

        return array_merge($defaults, $stored);
    }

    public function defaultCurrency(): string
    {
        return (string) ($this->all()['default_currency'] ?? 'KES');
    }

    public function invoicePrefix(): string
    {
        return (string) ($this->all()['invoice_prefix'] ?? 'INV');
    }

    public function vatRate(): float
    {
        return (float) ($this->all()['tax_rate'] ?? 0);
    }

    public function vatRegistered(): bool
    {
        return (bool) ($this->all()['vat_registered'] ?? false);
    }

    public function companyLegalName(): string
    {
        return (string) ($this->all()['company_legal_name'] ?? '');
    }

    public function taxPin(): string
    {
        return (string) ($this->all()['tax_pin'] ?? '');
    }

    public function paymentInstructions(): string
    {
        return (string) ($this->all()['payment_instructions'] ?? '');
    }

    public function defaultPaymentTerms(): string
    {
        return (string) ($this->all()['default_payment_terms'] ?? 'Net 30');
    }

    public function invoiceFooterNotes(): string
    {
        return (string) ($this->all()['invoice_footer_notes'] ?? '');
    }

    public function usageRatePerMb(): float
    {
        return (float) ($this->all()['usage_rate_per_mb'] ?? 0);
    }
}
