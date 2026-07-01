<?php

namespace App\Domain\Billing;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\View;

class DocumentRenderer
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
        private readonly DocumentIssuerResolver $issuerResolver,
    ) {}

    public function render(DocumentTemplate $template, array $snapshot, bool $forEmail = false): string
    {
        $issuer = $snapshot['issuer'] ?? $this->issuerResolver->resolve();
        $branding = array_merge($this->defaultBranding($issuer), $template->branding ?? []);

        $html = View::make($template->blade_view, [
            'snapshot' => $snapshot,
            'branding' => $branding,
            'issuer' => $issuer,
            'billing' => $this->billingSettings,
            'forEmail' => $forEmail,
        ])->render();

        if ($template->css) {
            $html = "<style>{$template->css}</style>".$html;
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $issuer
     * @return array<string, mixed>
     */
    private function defaultBranding(array $issuer): array
    {
        return [
            'company_name' => $issuer['legal_name'] ?: $this->billingSettings->companyLegalName() ?: config('app.name'),
            'display_name' => $issuer['display_name'] ?? $issuer['trading_name'] ?? $this->billingSettings->tradingName(),
            'trading_name' => $issuer['trading_name'] ?? $this->billingSettings->tradingName(),
            'legal_name' => $issuer['legal_name'] ?? $this->billingSettings->companyLegalName(),
            'tax_pin' => $issuer['pin'] ?? $this->billingSettings->taxPin(),
            'tagline' => $issuer['tagline'] ?? $this->billingSettings->issuerTagline(),
            'logo_url' => $issuer['logo_url'] ?? $this->billingSettings->logoUrl(),
            'primary_color' => '#111827',
            'accent_color' => '#facc15',
            'footer_text' => $this->billingSettings->invoiceFooterNotes(),
            'payment_instructions' => $this->billingSettings->paymentInstructions(),
            'show_qr' => false,
            'watermark' => null,
            'signature_label' => __('Authorized signature'),
        ];
    }
}
