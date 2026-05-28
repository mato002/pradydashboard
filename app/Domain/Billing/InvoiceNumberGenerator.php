<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Cache\OperationalCache;

class InvoiceNumberGenerator
{
    public function __construct(
        private readonly BillingSettings $settings,
        private readonly OperationalCache $operationalCache,
    ) {}

    public function next(string $documentType = BillingDocumentType::INVOICE): string
    {
        $year = now()->format('Y');
        $lockName = 'invoice:number:'.$documentType.':'.$year;

        $number = $this->operationalCache->lock(
            $lockName,
            config('redis_cache.locks.invoice_number', 15),
            fn () => $this->generateNumber($documentType),
        );

        if ($number === null) {
            return $this->generateNumber($documentType);
        }

        return $number;
    }

    private function generateNumber(string $documentType): string
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
