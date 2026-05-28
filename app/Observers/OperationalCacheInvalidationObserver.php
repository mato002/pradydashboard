<?php

namespace App\Observers;

use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use App\Support\Cache\CacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class OperationalCacheInvalidationObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        $invalidator = app(CacheInvalidator::class);

        match (true) {
            $model instanceof Tenant => $invalidator->forTenant($model),
            $model instanceof TenantInvoice => $invalidator->forInvoice($model),
            $model instanceof TenantPayment => $invalidator->forPayment($model),
            $model instanceof TenantProjectSubscription => $invalidator->forSubscription($model),
            $model instanceof SupportTicket => $invalidator->forSupportTicket($model),
            $model instanceof Server => $invalidator->forServer($model),
            default => null,
        };
    }
}
