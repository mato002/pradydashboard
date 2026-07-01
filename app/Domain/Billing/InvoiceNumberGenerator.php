<?php

namespace App\Domain\Billing;

use App\Support\Billing\BillingDocumentType;

/**
 * Backward-compatible facade — delegates numbering to DocumentIdentityService.
 */
class InvoiceNumberGenerator
{
    public function __construct(
        private readonly DocumentIdentityService $identity,
    ) {}

    public function next(string $documentType = BillingDocumentType::INVOICE): string
    {
        return $this->identity->generate($documentType);
    }
}
