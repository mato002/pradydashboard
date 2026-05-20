<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProjectInfrastructure extends Model
{
    protected $table = 'tenant_project_infrastructure';

    protected $fillable = [
        'tenant_project_subscription_id',
        'server_id',
        'cpanel_account',
        'whm_account_reference',
        'domain',
        'subdomain',
        'database_name',
        'database_user',
        'disk_quota_mb',
        'disk_used_mb',
        'bandwidth_quota_mb',
        'bandwidth_used_mb',
        'ssl_status',
        'ssl_expiry_date',
        'backup_policy',
        'backup_status',
        'last_backup_at',
        'deployment_path',
        'public_url',
        'admin_url',
        'health_check_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ssl_expiry_date' => 'date',
            'last_backup_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
