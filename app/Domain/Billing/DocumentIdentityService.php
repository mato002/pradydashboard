<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\DocumentNumberFormatter;
use App\Support\Billing\FinancialDocumentRegistry;
use App\Support\Cache\OperationalCache;

class DocumentIdentityService
{
    public function __construct(
        private readonly BillingSettings $settings,
        private readonly OperationalCache $operationalCache,
    ) {}

    public function generate(string $documentType = BillingDocumentType::INVOICE): string
    {
        $lockName = $this->sequenceScope($documentType).':'.now()->format('Y');

        $number = $this->operationalCache->lock(
            $lockName,
            config('redis_cache.locks.invoice_number', 15),
            fn () => $this->generateUnlocked($documentType),
        );

        return $number ?? $this->generateUnlocked($documentType);
    }

    public function generateUnlocked(string $documentType): string
    {
        if ($this->usesLegacyNumbering()) {
            return $this->generateLegacyNumber($documentType);
        }

        return $this->generateShortNumber($documentType);
    }

    public function formatDisplayNumber(?string $invoiceNumber): string
    {
        return DocumentNumberFormatter::display($invoiceNumber);
    }

    public function resolveTitle(string $documentType): string
    {
        return FinancialDocumentRegistry::title($documentType);
    }

    public function resolveLabel(string $documentType): string
    {
        return FinancialDocumentRegistry::label($documentType);
    }

    public function sequenceScope(string $documentType): string
    {
        return 'invoice:number:'.$documentType;
    }

    public function yearSuffix(?\DateTimeInterface $at = null): string
    {
        return ($at ?? now())->format('y');
    }

    public function usesLegacyNumbering(): bool
    {
        return $this->settings->usesLegacyNumbering();
    }

    public function numberSequencePadding(): int
    {
        return $this->settings->numberSequencePadding();
    }

    public function isShortFormat(string $invoiceNumber): bool
    {
        return DocumentNumberFormatter::isShortFormat($invoiceNumber);
    }

    private function generateShortNumber(string $documentType): string
    {
        $yearSuffix = $this->yearSuffix();
        $padding = $this->numberSequencePadding();
        $sequence = $this->nextShortSequence($documentType, $yearSuffix);

        while (true) {
            $sequenceStr = $padding > 0
                ? str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT)
                : (string) $sequence;

            $candidate = $sequenceStr.'/'.$yearSuffix;

            if (! $this->numberExists($documentType, $candidate)) {
                return $candidate;
            }

            $sequence++;
        }
    }

    private function nextShortSequence(string $documentType, string $yearSuffix): int
    {
        $max = 0;

        TenantInvoice::query()
            ->where('document_type', $documentType)
            ->pluck('invoice_number')
            ->each(function (string $number) use ($yearSuffix, &$max): void {
                if (DocumentNumberFormatter::parseShortYear($number) !== $yearSuffix) {
                    return;
                }

                $seq = DocumentNumberFormatter::parseShortSequence($number);
                if ($seq !== null) {
                    $max = max($max, $seq);
                }
            });

        return $max + 1;
    }

    private function generateLegacyNumber(string $documentType): string
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

    private function numberExists(string $documentType, string $number): bool
    {
        return TenantInvoice::query()
            ->where('document_type', $documentType)
            ->where('invoice_number', $number)
            ->exists();
    }
}
