<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantInvoice extends Model
{
    public const OPEN_STATUSES = ['draft', 'sent', 'pending', 'partial', 'partially_paid', 'overdue'];

    protected $fillable = [
        'tenant_id',
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
        'is_recurring',
        'pdf_generated',
        'email_delivered_at',
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
            'issued_at' => 'datetime',
            'is_recurring' => 'boolean',
            'pdf_generated' => 'boolean',
            'email_delivered_at' => 'datetime',
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

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
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
            'sent' => __('Delivered'),
            'failed' => __('Failed'),
            'pending' => __('Pending'),
            default => __('Not sent'),
        };
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
}
