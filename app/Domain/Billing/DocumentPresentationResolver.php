<?php

namespace App\Domain\Billing;

use App\Models\DocumentTemplate;
use App\Models\TenantInvoice;
use App\Support\Billing\FinancialDocumentRegistry;

class DocumentPresentationResolver
{
    public function __construct(
        private readonly DocumentIdentityService $identity,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(TenantInvoice $invoice, ?DocumentTemplate $template = null): array
    {
        $documentType = $invoice->document_type ?? 'invoice';
        $registry = FinancialDocumentRegistry::get($documentType);

        $templateFamily = $template?->style
            ?? $registry['default_template_family'];

        $paperSize = $template?->paper_size ?? $registry['paper_size'];
        $orientation = $template?->orientation ?? $registry['orientation'];

        return [
            'document_type' => $documentType,
            'document_title' => $this->identity->resolveTitle($documentType),
            'document_label' => $this->identity->resolveLabel($documentType),
            'display_number' => $this->identity->formatDisplayNumber($invoice->invoice_number),
            'invoice_number' => $invoice->invoice_number,
            'paper_size' => $paperSize,
            'orientation' => $orientation,
            'template_family' => $templateFamily,
            'template_id' => $template?->id ?? $invoice->document_template_id,
            'enabled_sections' => [
                'line_items' => (bool) $registry['has_line_items'],
                'payment_options' => (bool) $registry['shows_payment_options'],
                'paid_balance' => (bool) $registry['shows_paid_balance'],
                'statement_rows' => (bool) $registry['shows_statement_rows'],
            ],
            'lifecycle_badge' => [
                'label' => $invoice->lifecycleLabel(),
                'variant' => $invoice->lifecycleVariant(),
            ],
            'delivery_badge' => [
                'label' => $invoice->deliveryStatusLabel(),
                'variant' => $invoice->deliveryStatusVariant(),
            ],
        ];
    }
}
