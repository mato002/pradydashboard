<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DnsRecord extends Model
{
    protected $fillable = [
        'managed_domain_id',
        'record_type',
        'host',
        'value',
        'ttl',
        'propagation_status',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(ManagedDomain::class, 'managed_domain_id');
    }

    public function propagationVariant(): string
    {
        return match ($this->propagation_status) {
            'propagated' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'neutral',
        };
    }
}
