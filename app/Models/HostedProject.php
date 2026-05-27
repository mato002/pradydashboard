<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HostedProject extends Model
{
    protected $table = 'hosted_projects';

    protected $fillable = [
        'product_id',
        'server_id',
        'name',
        'domain',
        'base_url',
        'environment',
        'product_key',
        'api_token',
        'stack',
        'git_repository',
        'database_name',
        'cpanel_username',
        'status',
        'notes',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (HostedProject $hostedProject): void {
            if (empty($hostedProject->api_token)) {
                $hostedProject->api_token = Str::random(64);
            }
            if (empty($hostedProject->product_key) && $hostedProject->product) {
                $hostedProject->product_key = $hostedProject->product->slug;
            }
            if (empty($hostedProject->base_url) && filled($hostedProject->domain)) {
                $hostedProject->base_url = 'https://'.$hostedProject->domain;
            }
        });

        static::saving(function (HostedProject $hostedProject): void {
            if ($hostedProject->product_id && empty($hostedProject->product_key)) {
                $hostedProject->loadMissing('product');
                $hostedProject->product_key = $hostedProject->product?->slug;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function licenseCheckLogs(): HasMany
    {
        return $this->hasMany(LicenseCheckLog::class, 'hosted_project_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'hosted_project_id');
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(ProjectDeployment::class, 'hosted_project_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'hosted_project_id');
    }

    public function resolveProductKey(): string
    {
        return (string) ($this->product?->slug ?: $this->product_key ?: '');
    }
}
