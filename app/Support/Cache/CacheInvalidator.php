<?php

namespace App\Support\Cache;

use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;

class CacheInvalidator
{
    public function __construct(
        private readonly OperationalCache $cache,
    ) {}

    public function forTenant(Tenant $tenant): void
    {
        $this->cache->forgetTenant($tenant->id);
        $this->invalidateBillingSummaries();
        $this->invalidateSupportSummaries();
        $this->invalidateRiskSummaries();
    }

    public function forInvoice(TenantInvoice $invoice): void
    {
        if ($invoice->tenant_id) {
            $this->cache->forgetTenant((int) $invoice->tenant_id);
        }

        $this->invalidateBillingSummaries();
        $this->invalidateRiskSummaries();
    }

    public function forPayment(TenantPayment $payment): void
    {
        if ($payment->tenant_id) {
            $this->cache->forgetTenant((int) $payment->tenant_id);
        }

        $this->invalidateBillingSummaries();
    }

    public function forSubscription(TenantProjectSubscription $subscription): void
    {
        $this->cache->forgetTenant((int) $subscription->tenant_id);
        $this->invalidateBillingSummaries();
        $this->invalidateRiskSummaries();
    }

    public function forSupportTicket(SupportTicket $ticket): void
    {
        if ($ticket->tenant_id) {
            $this->cache->forgetTenant((int) $ticket->tenant_id);
        }

        $this->invalidateSupportSummaries();
        $this->invalidateRiskSummaries();
    }

    public function forServer(Server $server): void
    {
        $this->cache->forgetServer($server->id);
        $this->cache->bumpVersion('fleet');
    }

    public function invalidateBillingSummaries(): void
    {
        $this->cache->bumpVersion('billing');
        $this->cache->bumpVersion('financial');
    }

    public function invalidateSupportSummaries(): void
    {
        $this->cache->bumpVersion('support');
    }

    public function invalidateRiskSummaries(): void
    {
        $this->cache->bumpVersion('risk');
    }

    public function invalidateFleetSummaries(): void
    {
        $this->cache->bumpVersion('fleet');
    }
}
