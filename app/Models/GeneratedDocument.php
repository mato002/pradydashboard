<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedDocument extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_invoice_id',
        'type',
        'document_template_id',
        'html_snapshot',
        'data_snapshot',
        'pdf_path',
        'rendered_at',
        'rendered_by',
        'email_sent_at',
        'whatsapp_sent_at',
        'delivery_status',
    ];

    protected function casts(): array
    {
        return [
            'data_snapshot' => 'array',
            'rendered_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'whatsapp_sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class, 'tenant_invoice_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function isFinalized(): bool
    {
        return filled($this->html_snapshot);
    }

    public function pdfExists(): bool
    {
        return $this->pdf_path && Storage::disk('local')->exists($this->pdf_path);
    }
}
