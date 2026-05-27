<?php

namespace App\Support\PaymentsGateway;

class GatewayFormOptions
{
    /** @return list<string> */
    public static function systemTypes(): array
    {
        return ['mfi', 'property', 'erp', 'dashboard', 'spareme', 'other'];
    }

    /** @return list<string> */
    public static function tenantStatuses(): array
    {
        return ['active', 'suspended'];
    }

    /** @return list<string> */
    public static function paymentEnvironments(): array
    {
        return ['sandbox', 'production'];
    }

    /** @return list<string> */
    public static function paymentProfileStatuses(): array
    {
        return ['active', 'suspended'];
    }

    /** @return list<string> */
    public static function paybillAccountTypes(): array
    {
        return ['collection', 'disbursement', 'b2b', 'treasury', 'mixed'];
    }

    /** @return list<string> */
    public static function paybillAccountStatuses(): array
    {
        return ['active', 'suspended'];
    }

    /** @return list<string> */
    public static function webhookEndpointEvents(): array
    {
        return [
            'payment.received',
            'payment.completed',
            'payment.failed',
            'stk.completed',
            'stk.failed',
            'b2c.completed',
            'b2c.failed',
            'reversal.completed',
        ];
    }

    /** @return list<string> */
    public static function transactionTypes(): array
    {
        return ['stk', 'c2b', 'b2c', 'b2b', 'reversal', 'status_query'];
    }

    /** @return list<string> */
    public static function transactionStatuses(): array
    {
        return ['pending', 'processing', 'success', 'failed', 'cancelled', 'timeout'];
    }

    /** @return list<string> */
    public static function callbackTypes(): array
    {
        return ['stk', 'c2b_validation', 'c2b_confirmation', 'b2c_result', 'b2c_timeout'];
    }

    /** @return list<string> */
    public static function callbackProcessingStatuses(): array
    {
        return ['received', 'matched', 'processed', 'duplicate', 'failed', 'ignored'];
    }

    /** @return list<string> */
    public static function webhookEventTypes(): array
    {
        return [
            'payment.stk.success',
            'payment.stk.failed',
            'payment.c2b.received',
            'payment.b2c.success',
            'payment.b2c.failed',
            'payment.b2c.timeout',
            'payment.reversal.success',
            'payment.reversal.failed',
        ];
    }

    /** @return list<string> */
    public static function webhookEventStatuses(): array
    {
        return ['pending', 'queued', 'processing', 'delivered', 'failed', 'cancelled'];
    }

    /** @return list<string> */
    public static function webhookDeliveryStatuses(): array
    {
        return ['pending', 'success', 'failed'];
    }

    /** @return list<string> */
    public static function webhookEvents(): array
    {
        return [
            'payment.received',
            'payment.completed',
            'payment.failed',
            'stk.completed',
            'stk.failed',
            'b2c.completed',
            'b2c.failed',
            'reversal.completed',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $accounts
     * @return array<string, string>
     */
    public static function paybillAccountSelectOptions(array $accounts): array
    {
        $options = [];

        foreach ($accounts as $account) {
            $uuid = (string) ($account['uuid'] ?? '');

            if ($uuid === '') {
                continue;
            }

            $options[$uuid] = self::formatPaybillAccountLabel($account);
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>|null  $account
     */
    public static function formatPaybillAccountLabel(?array $account): string
    {
        if ($account === null || $account === []) {
            return '—';
        }

        $label = implode(' · ', array_filter([
            $account['account_name'] ?? null,
            $account['shortcode'] ?? null,
            isset($account['account_type']) ? ucfirst((string) $account['account_type']) : null,
        ]));

        if ($label !== '') {
            return $label;
        }

        return (string) ($account['uuid'] ?? '—');
    }
}
