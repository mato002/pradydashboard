<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantInvoice extends Model
{
    use HasPublicId;

    public const OPEN_STATUSES = ['draft', 'sent', 'pending', 'partial', 'partially_paid', 'overdue'];

    protected $fillable = [
        'tenant_id',
        'manual_client_name',
        'manual_client_email',
        'manual_client_phone',
        'manual_client_address',
        'tenant_project_subscription_id',
        'invoice_number',
        'document_type',
        'approval_status',
        'converted_at',
        'converted_invoice_id',
        'source_quotation_id',
        'delivery_status',
        'finalized_at',
        'revision_number',
        'last_reminder_at',
        'reminder_count',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'product_name',
        'amount_due',
        'amount_paid',
        'penalty_amount',
        'due_date',
        'issue_date',
        'issued_at',
        'status',
        'notes',
        'payment_method',
        'generated_by',
        'created_source',
        'document_template_id',
        'linked_invoice_id',
        'tenant_payment_id',
        'statement_period_start',
        'statement_period_end',
        'is_recurring',
        'pdf_generated',
        'email_delivered_at',
        'email_sent_at',
        'last_delivery_error',
        'collection_failed',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'due_date' => 'date',
            'issue_date' => 'date',
            'statement_period_start' => 'date',
            'statement_period_end' => 'date',
            'issued_at' => 'datetime',
            'is_recurring' => 'boolean',
            'pdf_generated' => 'boolean',
            'email_delivered_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'collection_failed' => 'boolean',
            'converted_at' => 'datetime',
            'finalized_at' => 'datetime',
            'last_reminder_at' => 'datetime',
            'revision_number' => 'integer',
            'reminder_count' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function projectSubscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(TenantInvoiceLineItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class);
    }

    public function collectionNotes(): HasMany
    {
        return $this->hasMany(CollectionNote::class);
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'converted_invoice_id');
    }

    public function sourceQuotation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_quotation_id');
    }

    public function linkedInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_invoice_id');
    }

    public function tenantPayment(): BelongsTo
    {
        return $this->belongsTo(TenantPayment::class, 'tenant_payment_id');
    }

    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function clientDisplayName(): string
    {
        return $this->tenant?->company_name
            ?? $this->manual_client_name
            ?? __('Unknown client');
    }

    public function pdfFilename(): string
    {
        $name = str_replace(['/', '\\'], '-', trim((string) $this->invoice_number));

        return ($name !== '' ? $name : 'document').'.pdf';
    }

    public function isManual(): bool
    {
        return $this->created_source === 'manual';
    }

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'pending'], true)
            || in_array($this->delivery_status, ['sent', 'resent'], true);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return in_array($this->status, ['partial', 'partially_paid'], true);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'void'], true);
    }

    public function isConverted(): bool
    {
        return $this->converted_invoice_id !== null
            || ($this->document_type === BillingDocumentType::PROFORMA && $this->converted_at !== null);
    }

    public function isExpired(): bool
    {
        if (! $this->due_date || $this->due_date->isFuture()) {
            return false;
        }

        if (! in_array($this->document_type, [BillingDocumentType::QUOTATION, BillingDocumentType::PROFORMA], true)) {
            return false;
        }

        return ! $this->isCancelled()
            && ! $this->isPaid()
            && ! $this->isConverted();
    }

    public function wasEmailed(): bool
    {
        return in_array($this->delivery_status, ['sent', 'resent'], true)
            || $this->email_sent_at !== null;
    }

    public function hasLinkedReceipt(): bool
    {
        if ($this->document_type !== BillingDocumentType::INVOICE) {
            return false;
        }

        return self::query()
            ->where('document_type', BillingDocumentType::RECEIPT)
            ->where('linked_invoice_id', $this->id)
            ->exists();
    }

    /**
     * @return array{label: string, variant: string}
     */
    public function lifecycleBadge(): array
    {
        $lifecycle = $this->lifecycleLabel();

        if ($lifecycle !== null) {
            return [
                'label' => $lifecycle,
                'variant' => $this->lifecycleVariant(),
            ];
        }

        return [
            'label' => $this->statusLabel(),
            'variant' => $this->statusVariant(),
        ];
    }

    public function canFinalize(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        if ($this->document_type === BillingDocumentType::RECEIPT) {
            return ! $this->isFinalized();
        }

        if ($this->document_type === BillingDocumentType::STATEMENT) {
            return ! $this->isFinalized();
        }

        if ($this->isConverted()) {
            return false;
        }

        return ! $this->isFinalized();
    }

    public function canSend(): bool
    {
        return ! $this->isCancelled() && $this->defaultRecipientEmail() !== null;
    }

    public function canCancel(): bool
    {
        if ($this->isCancelled() || $this->isPaid()) {
            return false;
        }

        if (in_array($this->document_type, [BillingDocumentType::RECEIPT, BillingDocumentType::STATEMENT], true)) {
            return false;
        }

        if ($this->isFinalized()) {
            return false;
        }

        return true;
    }

    public function canConvert(): bool
    {
        if ($this->isCancelled() || $this->isConverted()) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return match ($this->document_type) {
            BillingDocumentType::QUOTATION => $this->approval_status === 'approved',
            BillingDocumentType::PROFORMA => true,
            default => false,
        };
    }

    public function canRegenerate(): bool
    {
        return $this->regenerateBlockedReason() === null;
    }

    public function regenerateBlockedReason(): ?string
    {
        if ($this->document_type === BillingDocumentType::RECEIPT) {
            if ($this->wasEmailed()) {
                return __('Receipts cannot be regenerated after they have been emailed.');
            }

            if ($this->isFinalized()) {
                return __('Receipts are immutable proof of payment. Regeneration is not allowed.');
            }
        }

        if ($this->document_type === BillingDocumentType::STATEMENT && $this->wasEmailed()) {
            return __('Statements cannot be regenerated after they have been emailed.');
        }

        if ($this->document_type === BillingDocumentType::INVOICE && $this->isPaid()) {
            return __('Paid invoices cannot be regenerated.');
        }

        if ($this->document_type === BillingDocumentType::INVOICE && $this->hasLinkedReceipt()) {
            return __('Invoices with issued receipts cannot be regenerated.');
        }

        return null;
    }

    public function canRecordPayment(): bool
    {
        if ($this->document_type !== BillingDocumentType::INVOICE) {
            return false;
        }

        return ! in_array($this->status, ['cancelled', 'void', 'paid', 'draft'], true);
    }

    public function agingDays(): ?int
    {
        if (! $this->due_date) {
            return null;
        }

        return (int) now()->diffInDays($this->due_date, false);
    }

    public function agingLabel(): string
    {
        $days = $this->agingDays();
        if ($days === null) {
            return '—';
        }

        if ($days >= 0) {
            return __('Current');
        }

        return __(':days d overdue', ['days' => abs($days)]);
    }

    public function agingColor(): string
    {
        $days = $this->agingDays();
        if ($days === null || $days >= 0) {
            return 'text-emerald-600';
        }
        if ($days >= -30) {
            return 'text-amber-600';
        }

        return 'text-rose-600';
    }

    public function documentTypeLabel(): string
    {
        return \App\Support\Billing\BillingDocumentType::label($this->document_type ?? 'invoice');
    }

    public function deliveryStatusLabel(): string
    {
        return match ($this->delivery_status) {
            'sent' => __('Sent'),
            'resent' => __('Resent'),
            'failed' => __('Failed'),
            'pending' => __('Not sent'),
            'not_sent' => __('Not sent'),
            default => __('Not sent'),
        };
    }

    public function deliveryStatusVariant(): string
    {
        return match ($this->delivery_status) {
            'sent', 'resent' => 'success',
            'failed' => 'danger',
            default => 'neutral',
        };
    }

    public function defaultRecipientEmail(): ?string
    {
        $email = trim((string) ($this->tenant?->billing_email ?? $this->manual_client_email ?? ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function invoiceTotal(): float
    {
        return (float) (($this->total > 0) ? $this->total : $this->amount_due);
    }

    public function balanceDue(): float
    {
        return max(0, $this->invoiceTotal() - (float) $this->amount_paid + (float) $this->penalty_amount);
    }

    /** @deprecated Use balanceDue() */
    public function balance(): float
    {
        return $this->balanceDue();
    }

    public function syncPaymentStatus(): void
    {
        if (in_array($this->status, ['cancelled', 'void'], true)) {
            return;
        }

        $balance = $this->balanceDue();

        if ($balance <= 0.009) {
            $this->status = 'paid';
            $this->amount_paid = $this->invoiceTotal() + (float) $this->penalty_amount;

            return;
        }

        if ((float) $this->amount_paid > 0) {
            $this->status = 'partially_paid';

            return;
        }

        if ($this->status === 'draft') {
            return;
        }

        if ($this->due_date && $this->due_date->isPast() && in_array($this->status, ['sent', 'pending', 'partially_paid', 'partial'], true)) {
            $this->status = 'overdue';
        }
    }

    public function formattedAmount(?string $currency = null): string
    {
        return self::formatMoney($this->invoiceTotal(), $currency ?? $this->currency);
    }

    public function formattedBalance(?string $currency = null): string
    {
        return self::formatMoney($this->balanceDue(), $currency ?? $this->currency);
    }

    public static function formatMoney(float $amount, ?string $currency = 'KES'): string
    {
        $code = $currency ?? 'KES';

        if ($amount >= 1_000_000) {
            return $code.' '.number_format($amount / 1_000_000, 2).'M';
        }
        if ($amount >= 1_000) {
            return $code.' '.number_format($amount / 1_000, 1).'K';
        }

        return $code.' '.number_format($amount, 2);
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'partial', 'partially_paid' => 'info',
            'overdue' => 'danger',
            'cancelled', 'void' => 'neutral',
            'sent' => 'sky',
            'draft' => 'neutral',
            default => 'warning',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => __('Paid'),
            'partial', 'partially_paid' => __('Partially paid'),
            'overdue' => __('Overdue'),
            'cancelled', 'void' => __('Cancelled'),
            'sent' => __('Sent'),
            'draft' => __('Draft'),
            default => __('Pending'),
        };
    }

    public function lifecycleLabel(): ?string
    {
        if ($this->converted_invoice_id) {
            return __('Converted');
        }

        if ($this->document_type === BillingDocumentType::QUOTATION) {
            return match ($this->approval_status) {
                'approved' => __('Approved'),
                'pending' => __('Pending approval'),
                'rejected' => __('Rejected'),
                default => null,
            };
        }

        if ($this->document_type === BillingDocumentType::PROFORMA && $this->converted_at) {
            return __('Converted');
        }

        if ($this->document_type === BillingDocumentType::RECEIPT && $this->linked_invoice_id) {
            $linked = $this->relationLoaded('linkedInvoice')
                ? $this->linkedInvoice
                : null;

            if ($linked && in_array($linked->status, ['cancelled', 'void'], true)) {
                return __('Source invoice cancelled');
            }

            return __('Linked');
        }

        if ($this->document_type === BillingDocumentType::STATEMENT) {
            return $this->finalized_at ? __('Generated') : __('Draft');
        }

        if ($this->due_date && $this->due_date->isPast()
            && in_array($this->document_type, [BillingDocumentType::QUOTATION, BillingDocumentType::PROFORMA], true)
            && ! in_array($this->status, ['paid', 'cancelled', 'void'], true)
            && ! $this->converted_invoice_id) {
            return __('Expired');
        }

        return null;
    }

    public function lifecycleVariant(): string
    {
        if ($this->converted_invoice_id || ($this->document_type === BillingDocumentType::PROFORMA && $this->converted_at)) {
            return 'success';
        }

        if ($this->document_type === BillingDocumentType::QUOTATION) {
            return match ($this->approval_status) {
                'approved' => 'success',
                'rejected' => 'danger',
                'pending' => 'warning',
                default => 'neutral',
            };
        }

        if ($this->document_type === BillingDocumentType::RECEIPT && $this->linked_invoice_id) {
            $linked = $this->relationLoaded('linkedInvoice') ? $this->linkedInvoice : null;
            if ($linked && in_array($linked->status, ['cancelled', 'void'], true)) {
                return 'warning';
            }

            return 'success';
        }

        if ($this->document_type === BillingDocumentType::STATEMENT && $this->finalized_at) {
            return 'success';
        }

        if ($this->due_date && $this->due_date->isPast()
            && in_array($this->document_type, [BillingDocumentType::QUOTATION, BillingDocumentType::PROFORMA], true)
            && ! in_array($this->status, ['paid', 'cancelled', 'void'], true)
            && ! $this->converted_invoice_id) {
            return 'danger';
        }

        return 'neutral';
    }
}
