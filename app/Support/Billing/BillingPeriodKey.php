<?php

namespace App\Support\Billing;

use Carbon\Carbon;
use Illuminate\Database\QueryException;

final class BillingPeriodKey
{
    public static function forDraftTenant(int $tenantId, Carbon $periodStart, Carbon $periodEnd): string
    {
        return 'draft:t'.$tenantId.'|'.$periodStart->toDateString().'|'.$periodEnd->toDateString();
    }

    public static function forDraftProjectSubscription(int $subscriptionId, Carbon $periodStart, Carbon $periodEnd): string
    {
        return 'draft:tps'.$subscriptionId.'|'.$periodStart->toDateString().'|'.$periodEnd->toDateString();
    }

    public static function forRecurringSchedule(int $scheduleId, Carbon $periodStart, Carbon $periodEnd): string
    {
        return 'recurring:s'.$scheduleId.'|'.$periodStart->toDateString().'|'.$periodEnd->toDateString();
    }

    public static function isDuplicatePeriodKeyException(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'subscription_period_key')
            || str_contains($message, 'tenant_invoices_subscription_period_key_unique');
    }
}
