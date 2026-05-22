<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\CollectionNote;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use App\Support\Billing\CollectionNoteOutcome;
use App\Support\Billing\CollectionNoteStatus;

class CollectionWorkflowService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function addNote(TenantInvoice $invoice, array $data): CollectionNote
    {
        $note = CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'user_id' => auth()->id(),
            'note_type' => $data['note_type'] ?? 'general',
            'body' => $data['note'] ?? $data['body'] ?? '',
            'note' => $data['note'] ?? $data['body'] ?? '',
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'promise_to_pay_date' => $data['promise_to_pay_date'] ?? null,
            'promised_amount' => $data['promised_amount'] ?? null,
            'promised_at' => $data['promise_to_pay_date'] ?? $data['promised_at'] ?? null,
            'status' => $data['status'] ?? CollectionNoteStatus::OPEN,
            'outcome' => $data['outcome'] ?? null,
        ]);

        $this->activityLogger->log(
            'collection.note_added',
            ActivityLogCategory::BILLING,
            __('Collection note on :number', ['number' => $invoice->invoice_number]),
            $invoice,
            null,
            ['collection_note_id' => $note->id],
        );

        return $note;
    }

    public function completeFollowUp(CollectionNote $note): CollectionNote
    {
        $note->update(['status' => CollectionNoteStatus::COMPLETED]);
        $invoice = $note->invoice;

        if ($invoice) {
            $this->activityLogger->log(
                'collection.follow_up_completed',
                ActivityLogCategory::BILLING,
                __('Follow-up completed for :number', ['number' => $invoice->invoice_number]),
                $invoice,
                null,
                ['collection_note_id' => $note->id],
            );
        }

        return $note->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markDisputed(TenantInvoice $invoice, array $data = []): CollectionNote
    {
        $note = $this->addNote($invoice, [
            'note_type' => 'dispute',
            'note' => $data['note'] ?? __('Invoice marked as disputed.'),
            'outcome' => CollectionNoteOutcome::DISPUTED,
            'follow_up_date' => $data['follow_up_date'] ?? null,
        ]);

        $this->activityLogger->log(
            'collection.invoice_disputed',
            ActivityLogCategory::BILLING,
            __('Invoice :number disputed', ['number' => $invoice->invoice_number]),
            $invoice,
        );

        return $note;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordPromiseToPay(TenantInvoice $invoice, array $data): CollectionNote
    {
        $note = $this->addNote($invoice, [
            'note_type' => 'promise',
            'note' => $data['note'] ?? __('Promise to pay recorded.'),
            'outcome' => CollectionNoteOutcome::PROMISED_PAYMENT,
            'promise_to_pay_date' => $data['promise_to_pay_date'] ?? null,
            'promised_amount' => $data['promised_amount'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? $data['promise_to_pay_date'] ?? null,
        ]);

        $this->activityLogger->log(
            'collection.promise_to_pay',
            ActivityLogCategory::BILLING,
            __('Promise to pay on :number', ['number' => $invoice->invoice_number]),
            $invoice,
            null,
            [
                'promised_amount' => $data['promised_amount'] ?? null,
                'promise_to_pay_date' => $data['promise_to_pay_date'] ?? null,
            ],
        );

        return $note;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function escalate(TenantInvoice $invoice, array $data = []): CollectionNote
    {
        $note = $this->addNote($invoice, [
            'note_type' => 'escalation',
            'note' => $data['note'] ?? __('Escalated for management review.'),
            'outcome' => CollectionNoteOutcome::ESCALATED,
            'follow_up_date' => $data['follow_up_date'] ?? now()->addDays(2)->toDateString(),
        ]);

        $this->activityLogger->log(
            'collection.escalated',
            ActivityLogCategory::BILLING,
            __('Invoice :number escalated', ['number' => $invoice->invoice_number]),
            $invoice,
            null,
            ['collection_note_id' => $note->id],
        );

        return $note;
    }
}
