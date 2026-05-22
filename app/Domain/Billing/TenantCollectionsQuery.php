<?php

namespace App\Domain\Billing;

use App\Models\CollectionNote;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\CollectionNoteOutcome;
use App\Support\Billing\CollectionNoteStatus;
use Illuminate\Support\Collection;

class TenantCollectionsQuery
{
    /**
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $invoices = TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->with('lineItems')
            ->orderByDesc('due_date')
            ->get();

        $unpaid = $invoices->filter(fn (TenantInvoice $i) => $i->balanceDue() > 0.009
            && ! in_array($i->status, ['paid', 'cancelled', 'void', 'draft'], true));

        $overdue = $unpaid->filter(fn (TenantInvoice $i) => $i->due_date && $i->due_date->isPast());

        $notes = CollectionNote::query()
            ->where(function ($q) use ($tenant): void {
                $q->where('tenant_id', $tenant->id)
                    ->orWhereIn('tenant_invoice_id', TenantInvoice::query()->where('tenant_id', $tenant->id)->select('id'));
            })
            ->open()
            ->with('invoice')
            ->latest()
            ->limit(20)
            ->get();

        $promises = $notes->where('outcome', CollectionNoteOutcome::PROMISED_PAYMENT)
            ->where('status', CollectionNoteStatus::OPEN);

        $nextFollowUp = $notes->where('status', CollectionNoteStatus::OPEN)
            ->whereNotNull('follow_up_date')
            ->sortBy('follow_up_date')
            ->first();

        return [
            'unpaid_invoices' => $unpaid->values(),
            'overdue_invoices' => $overdue->values(),
            'open_collection_notes' => $notes,
            'collection_notes' => $notes,
            'promises' => $promises->values(),
            'next_follow_up' => $nextFollowUp?->follow_up_date,
        ];
    }
}
