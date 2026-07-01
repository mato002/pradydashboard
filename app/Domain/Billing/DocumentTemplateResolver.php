<?php

namespace App\Domain\Billing;

use App\Models\DocumentTemplate;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\FinancialDocumentRegistry;

class DocumentTemplateResolver
{
    public function defaultTemplateId(string $documentType, ?string $style = null): ?int
    {
        $style ??= FinancialDocumentRegistry::defaultTemplateFamily($documentType);
        $query = DocumentTemplate::query()
            ->where('type', $documentType)
            ->where('active', true)
            ->orderByDesc('is_default');

        if ($style !== null) {
            $styled = (clone $query)->where('style', $style)->value('id');
            if ($styled) {
                return (int) $styled;
            }
        }

        return $query->value('id');
    }

    public function defaultInvoiceTemplateId(): ?int
    {
        return $this->defaultTemplateId(BillingDocumentType::INVOICE);
    }

    public function defaultReceiptTemplateId(): ?int
    {
        return $this->defaultTemplateId(BillingDocumentType::RECEIPT);
    }
}
