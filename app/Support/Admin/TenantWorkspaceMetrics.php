<?php

namespace App\Support\Admin;

use App\Models\Tenant;

/**
 * Human-readable, grouped KPI cards for the tenant profile shell.
 */
class TenantWorkspaceMetrics
{
    /**
     * @param  array<string, mixed>  $ops
     * @param  array<string, mixed>  $billing
     * @return list<array{id: string, label: string, items: list<array<string, mixed>>}>
     */
    public static function groups(array $ops, array $billing, Tenant $tenant): array
    {
        $currency = $ops['currency'] ?? $tenant->tenant_currency ?? 'KES';
        $mrr = number_format((float) ($ops['monthly_revenue'] ?? 0), 0);
        $outstanding = number_format((float) ($billing['outstanding'] ?? 0), 2);
        $moduleBilling = (float) ($ops['modules_billing_total'] ?? 0);
        $ssl = strtolower((string) ($ops['ssl_status'] ?? ''));
        $backup = strtolower((string) ($ops['backup_status'] ?? ''));
        $version = strtolower(str_replace(' ', '_', (string) ($ops['version_status'] ?? '')));

        return [
            [
                'id' => 'billing',
                'label' => __('Billing'),
                'items' => [
                    self::item(
                        __('Monthly recurring revenue'),
                        "{$currency} {$mrr}",
                        'currency',
                        (float) ($ops['monthly_revenue'] ?? 0) > 0 ? 'success' : 'neutral',
                    ),
                    self::item(
                        __('Outstanding balance'),
                        "{$currency} {$outstanding}",
                        'receipt',
                        (float) ($billing['outstanding'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                    self::item(
                        __('Renewal attention'),
                        (int) ($ops['renewal_risk'] ?? 0) > 0
                            ? __(':count within 14 days', ['count' => $ops['renewal_risk']])
                            : __('No renewals due soon'),
                        'calendar',
                        (int) ($ops['renewal_risk'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                    self::item(
                        __('Plan amount'),
                        $tenant->subscription_amount !== null
                            ? "{$currency} ".number_format((float) $tenant->subscription_amount, 2)
                            : __('Billing plan not configured'),
                        'credit-card',
                        $tenant->subscription_amount !== null ? 'neutral' : 'neutral',
                    ),
                ],
            ],
            [
                'id' => 'infrastructure',
                'label' => __('Infrastructure'),
                'items' => [
                    self::item(
                        __('Hosting server'),
                        (string) ($ops['assigned_server'] ?? __('Unassigned')),
                        'server',
                        str_contains(strtolower((string) ($ops['assigned_server'] ?? '')), 'unassigned') ? 'warning' : 'success',
                    ),
                    self::item(
                        __('SSL monitoring'),
                        self::sslLabel($ssl),
                        'shield',
                        in_array($ssl, ['valid', 'active', 'ok'], true) ? 'success' : ($ssl === 'unknown' || $ssl === '' ? 'neutral' : 'warning'),
                    ),
                    self::item(
                        __('Backup policy'),
                        self::backupLabel($backup),
                        'archive',
                        in_array($backup, ['ok', 'healthy', 'enabled'], true) ? 'success' : ($backup === 'unknown' || $backup === '' ? 'neutral' : 'warning'),
                    ),
                    self::item(
                        __('Deployment version'),
                        self::versionLabel($version, (string) ($ops['update_risk'] ?? '')),
                        'code',
                        in_array($version, ['outdated', 'critical_update_required'], true) ? 'danger' : 'success',
                    ),
                    self::item(
                        __('Server assignment gaps'),
                        (int) ($ops['infrastructure_gaps'] ?? 0) > 0
                            ? __(':count subscriptions need a server', ['count' => $ops['infrastructure_gaps']])
                            : __('All required servers assigned'),
                        'alert-triangle',
                        (int) ($ops['infrastructure_gaps'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                ],
            ],
            [
                'id' => 'licensing',
                'label' => __('Licensing'),
                'items' => [
                    self::item(
                        __('License compliance'),
                        (int) ($ops['license_issues'] ?? 0) > 0
                            ? __(':count need attention', ['count' => $ops['license_issues']])
                            : __('All licenses healthy'),
                        'key',
                        (int) ($ops['license_issues'] ?? 0) > 0 ? 'danger' : 'success',
                    ),
                    self::item(
                        __('Version drift'),
                        (int) ($ops['outdated_versions'] ?? 0) > 0
                            ? __(':count behind latest', ['count' => $ops['outdated_versions']])
                            : __('Versions up to date'),
                        'git-branch',
                        (int) ($ops['outdated_versions'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                    self::item(
                        __('Tenant system API'),
                        ! empty($ops['tenant_system_configured'])
                            ? __('Connected · :health', ['health' => ucfirst((string) ($ops['tenant_system_health'] ?? 'ok'))])
                            : __('Tenant system API not configured'),
                        'plug',
                        ! empty($ops['tenant_system_configured']) ? 'success' : 'neutral',
                    ),
                ],
            ],
            [
                'id' => 'integrations',
                'label' => __('Integrations'),
                'items' => [
                    self::item(
                        __('Active integrations'),
                        (string) ($ops['integrations_active'] ?? 0),
                        'link',
                        'success',
                    ),
                    self::item(
                        __('Failing integrations'),
                        (int) ($ops['integrations_failing'] ?? 0) > 0
                            ? __(':count failing', ['count' => $ops['integrations_failing']])
                            : __('No failing integrations'),
                        'unlink',
                        (int) ($ops['integrations_failing'] ?? 0) > 0 ? 'danger' : 'success',
                    ),
                    self::item(
                        __('Untested integrations'),
                        (int) ($ops['integrations_not_tested'] ?? 0) > 0
                            ? __(':count awaiting test', ['count' => $ops['integrations_not_tested']])
                            : __('All integrations tested'),
                        'beaker',
                        (int) ($ops['integrations_not_tested'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                ],
            ],
            [
                'id' => 'support',
                'label' => __('Support & docs'),
                'items' => [
                    self::item(
                        __('Open support tickets'),
                        (int) ($ops['open_tickets'] ?? 0) > 0
                            ? (string) $ops['open_tickets']
                            : __('No open tickets'),
                        'life-buoy',
                        (int) ($ops['open_tickets'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                    self::item(
                        __('Operational documents'),
                        __(':count on file', ['count' => $ops['documents_count'] ?? 0]),
                        'file-text',
                        'neutral',
                    ),
                    self::item(
                        __('Expiring documents'),
                        (int) ($ops['documents_expiring'] ?? 0) > 0
                            ? __(':count expiring soon', ['count' => $ops['documents_expiring']])
                            : __('No upcoming expirations'),
                        'clock',
                        (int) ($ops['documents_expiring'] ?? 0) > 0 ? 'warning' : 'success',
                    ),
                    self::item(
                        __('Missing contracts'),
                        (int) ($ops['missing_required_contracts'] ?? 0) > 0
                            ? __(':count required', ['count' => $ops['missing_required_contracts']])
                            : __('Contracts complete'),
                        'file-warning',
                        (int) ($ops['missing_required_contracts'] ?? 0) > 0 ? 'danger' : 'success',
                    ),
                ],
            ],
            [
                'id' => 'modules',
                'label' => __('Modules'),
                'items' => [
                    self::item(
                        __('Active projects'),
                        (string) ($ops['active_projects'] ?? 0),
                        'layers',
                        'neutral',
                    ),
                    self::item(
                        __('Modules enabled'),
                        __(':enabled of :subscribed', [
                            'enabled' => $ops['modules_enabled'] ?? 0,
                            'subscribed' => $ops['modules_subscribed'] ?? 0,
                        ]),
                        'grid',
                        'neutral',
                    ),
                    self::item(
                        __('Module billing'),
                        $moduleBilling > 0
                            ? ($ops['modules_currency'] ?? $currency).' '.number_format($moduleBilling, 2)
                            : __('Monthly module billing not configured'),
                        'wallet',
                        $moduleBilling > 0 ? 'success' : 'neutral',
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, value: string, icon: string, tone: string}
     */
    private static function item(string $label, string $value, string $icon, string $tone): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'tone' => $tone,
        ];
    }

    private static function sslLabel(string $status): string
    {
        return match ($status) {
            'valid', 'active', 'ok' => __('SSL certificate healthy'),
            'expiring', 'expiring_soon' => __('SSL certificate expiring soon'),
            'expired', 'invalid' => __('SSL certificate needs renewal'),
            'unknown', '' => __('SSL monitoring not configured'),
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private static function backupLabel(string $status): string
    {
        return match ($status) {
            'ok', 'healthy', 'enabled' => __('Backups reporting healthy'),
            'failed', 'error' => __('Backup failures detected'),
            'unknown', '' => __('Backup monitoring not configured'),
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private static function versionLabel(string $status, string $updateRisk): string
    {
        if ($status === 'critical_update_required') {
            return __('Critical update required');
        }
        if ($status === 'outdated') {
            return __('Running behind latest release');
        }
        if ($status === 'unknown' || $status === '') {
            return __('Version tracking not configured');
        }

        if ($updateRisk !== '' && $updateRisk !== __('OK')) {
            return ucfirst($status).' · '.$updateRisk;
        }

        return __('On supported version');
    }
}
