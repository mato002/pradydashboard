<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Server extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'ip_address',
        'whm_cpanel_reference',
        'cpu_cores',
        'ram_gb',
        'storage_gb',
        'disk_usage_percent',
        'status',
        'ssl_status',
        'backup_status',
        'hosted_domains',
        'renewal_expires_at',
        'monthly_cost',
        'currency',
        'monthly_revenue',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hosted_domains' => 'array',
            'renewal_expires_at' => 'date',
            'monthly_cost' => 'decimal:2',
            'monthly_revenue' => 'decimal:2',
            'ram_gb' => 'decimal:2',
            'storage_gb' => 'decimal:2',
            'disk_usage_percent' => 'decimal:2',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function healthLogs(): HasMany
    {
        return $this->hasMany(ServerHealthLog::class);
    }

    public function latestHealthLog(): HasOne
    {
        return $this->hasOne(ServerHealthLog::class)->latestOfMany('checked_at');
    }

    public function monthlyProfit(): string
    {
        $rev = (float) ($this->monthly_revenue ?? 0);
        $cost = (float) ($this->monthly_cost ?? 0);

        return number_format($rev - $cost, 2, '.', '');
    }
}
