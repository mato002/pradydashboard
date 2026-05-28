<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\DocumentDeliveryService;
use App\Jobs\OperationalJob;
use App\Models\TenantInvoice;
use App\Support\Queue\QueueName;

class GenerateFinancialDocumentPdfJob extends OperationalJob
{
    public function __construct(
        public int $invoiceId,
        public bool $forceFinalize = false,
    ) {
        $this->onQueue(QueueName::PDF);
        $this->timeout = 180;
    }

    public function handle(DocumentDeliveryService $delivery): void
    {
        $this->withLock(
            'job:pdf:invoice:'.$this->invoiceId,
            120,
            function () use ($delivery): void {
                $invoice = TenantInvoice::query()->find($this->invoiceId);
                if (! $invoice) {
                    return;
                }

                if ($this->forceFinalize) {
                    $delivery->ensureDocument($invoice, true);
                }

                $delivery->ensurePdf($invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']));
            },
        );
    }
}
