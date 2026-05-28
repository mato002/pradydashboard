<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\InvoiceEmailDelivery;
use App\Jobs\OperationalJob;
use App\Models\GeneratedDocument;
use App\Models\TenantInvoice;
use App\Support\Queue\QueueName;

class SendFinancialDocumentEmailJob extends OperationalJob
{
    public function __construct(
        public int $invoiceId,
        public int $documentId,
        public string $recipientEmail,
        public bool $isResend = false,
    ) {
        $this->onQueue(QueueName::EMAILS);
    }

    public function handle(InvoiceEmailDelivery $emailDelivery): void
    {
        $this->withLock(
            'job:email:invoice:'.$this->invoiceId,
            config('redis_cache.locks.payment_reference', 30),
            function () use ($emailDelivery): void {
                $invoice = TenantInvoice::query()->find($this->invoiceId);
                $document = GeneratedDocument::query()->find($this->documentId);

                if (! $invoice || ! $document || (int) $document->tenant_invoice_id !== (int) $invoice->id) {
                    return;
                }

                if (! $this->isResend && in_array($invoice->delivery_status, ['sent', 'resent'], true)) {
                    return;
                }

                $emailDelivery->send($invoice, $document, $this->recipientEmail, $this->isResend);
            },
        );
    }
}
