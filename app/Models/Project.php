<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'server_id',
        'name',
        'domain',
        'product_slug',
        'technology_stack',
        'git_repository',
        'database_name',
        'status',
        'version',
        'monthly_revenue',
        'monthly_cost',
        'api_token',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_revenue' => 'decimal:2',
            'monthly_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            if (empty($project->api_token)) {
                $project->api_token = Str::random(64);
            }
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(ProjectDeployment::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function monthlyProfit(): string
    {
        $rev = (float) ($this->monthly_revenue ?? 0);
        $cost = (float) ($this->monthly_cost ?? 0);

        return number_format($rev - $cost, 2, '.', '');
    }
}
