<?php

namespace App\Domain\Billing;

use App\Domain\Settings\PlatformSettingsService;
use App\Models\Setting;

class DocumentIssuerResolver
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
    ) {}

    /**
     * Issuer identity for document snapshots (not hardcoded in Blade).
     *
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        $company = array_merge(
            (new PlatformSettingsService)->defaults('company'),
            Setting::getJson('platform.company'),
        );

        $legalName = $this->billingSettings->companyLegalName()
            ?: (string) ($company['legal_name'] ?? '');

        $tradingName = $this->billingSettings->tradingName()
            ?: ($legalName !== '' ? $legalName : (string) ($company['legal_name'] ?? config('app.name')));

        return [
            'legal_name' => $legalName,
            'trading_name' => $tradingName,
            'display_name' => $tradingName !== '' ? $tradingName : $legalName,
            'pin' => $this->billingSettings->taxPin(),
            'phone' => $this->billingSettings->issuerPhone() ?: (string) ($company['phone'] ?? ''),
            'email' => $this->billingSettings->issuerEmail() ?: (string) ($company['billing_email'] ?? ''),
            'website' => $this->billingSettings->issuerWebsite(),
            'address' => $this->billingSettings->issuerAddress() ?: (string) ($company['address'] ?? ''),
            'tagline' => $this->billingSettings->issuerTagline(),
            'logo_url' => $this->billingSettings->logoUrl(),
        ];
    }
}
