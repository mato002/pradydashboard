<?php

namespace App\Domain\Billing;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\View;

class DocumentRenderer
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
    ) {}

    public function render(DocumentTemplate $template, array $snapshot, bool $forEmail = false): string
    {
        $branding = array_merge($this->defaultBranding(), $template->branding ?? []);

        $html = View::make($template->blade_view, [
            'snapshot' => $snapshot,
            'branding' => $branding,
            'billing' => $this->billingSettings,
            'forEmail' => $forEmail,
        ])->render();

        if ($template->css) {
            $html = "<style>{$template->css}</style>".$html;
        }

        return $html;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultBranding(): array
    {
        return [
            'company_name' => $this->billingSettings->companyLegalName() ?: config('app.name'),
            'tax_pin' => $this->billingSettings->taxPin(),
            'primary_color' => '#4f46e5',
            'accent_color' => '#f59e0b',
            'footer_text' => $this->billingSettings->invoiceFooterNotes(),
            'payment_instructions' => $this->billingSettings->paymentInstructions(),
            'show_qr' => false,
            'watermark' => null,
            'signature_label' => __('Authorized signature'),
        ];
    }
}
