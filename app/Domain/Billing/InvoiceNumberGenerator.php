<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;

class InvoiceNumberGenerator
{
    public function __construct(
        private readonly BillingSettings $settings,
    ) {}

    public function next(string $documentType = BillingDocumentType::INVOICE): string
    {
        $prefix = $documentType === BillingDocumentType::INVOICE
            ? $this->settings->invoicePrefix()
            : BillingDocumentType::numberPrefix($documentType);

        $year = now()->format('Y');
        $sequence = TenantInvoice::query()
            ->where('document_type', $documentType)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }
}
