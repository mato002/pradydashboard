<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeploymentIntegration extends Model
{
    protected $fillable = [
        'provider',
        'name',
        'status',
        'repositories_count',
        'webhooks_count',
        'settings',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(DeploymentWebhookEvent::class);
    }
}
