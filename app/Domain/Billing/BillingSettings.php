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
        $envOverrides = array_filter(
            config('billing', []),
            static fn ($value) => $value !== null && $value !== '',
        );

        return array_merge($defaults, $stored, $envOverrides);
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

    public function tradingName(): string
    {
        return (string) ($this->all()['trading_name'] ?? '');
    }

    public function taxPin(): string
    {
        return (string) ($this->all()['tax_pin'] ?? '');
    }

    public function issuerPhone(): string
    {
        return (string) ($this->all()['issuer_phone'] ?? '');
    }

    public function issuerEmail(): string
    {
        return (string) ($this->all()['issuer_email'] ?? '');
    }

    public function issuerWebsite(): string
    {
        return (string) ($this->all()['issuer_website'] ?? '');
    }

    public function issuerAddress(): string
    {
        return (string) ($this->all()['issuer_address'] ?? '');
    }

    public function issuerTagline(): string
    {
        return (string) ($this->all()['issuer_tagline'] ?? '');
    }

    public function logoUrl(): ?string
    {
        $url = trim((string) ($this->all()['logo_url'] ?? ''));

        return $url !== '' ? $url : null;
    }

    public function bankAccountName(): string
    {
        return (string) ($this->all()['bank_account_name'] ?? '');
    }

    public function usesLegacyNumbering(): bool
    {
        return ($this->all()['numbering_style'] ?? 'short') === 'legacy';
    }

    public function numberSequencePadding(): int
    {
        return max(0, (int) ($this->all()['number_sequence_padding'] ?? 0));
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

    public function bankName(): string
    {
        return (string) ($this->all()['bank_name'] ?? '');
    }

    public function bankAccountNumber(): string
    {
        return (string) ($this->all()['bank_account_number'] ?? '');
    }

    public function bankBranch(): string
    {
        return (string) ($this->all()['bank_branch'] ?? '');
    }

    public function mpesaPaybill(): string
    {
        return (string) ($this->all()['mpesa_paybill'] ?? '');
    }

    public function paybillAccountNumber(): string
    {
        return (string) ($this->all()['paybill_account_number'] ?? '');
    }

    public function billingFromEmail(): string
    {
        $configured = (string) ($this->all()['billing_from_email'] ?? '');

        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
            return $configured;
        }

        return (string) config('mail.from.address', 'billing@example.com');
    }
}
