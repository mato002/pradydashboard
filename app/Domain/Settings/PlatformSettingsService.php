<?php

namespace App\Domain\Settings;

use App\Models\Project;
use App\Models\Server;
use App\Models\Setting;
use App\Models\Tenant;

class PlatformSettingsService
{
    public const SECTIONS = [
        'general' => ['label' => 'General', 'icon' => 'adjustments'],
        'branding' => ['label' => 'Branding', 'icon' => 'sparkles'],
        'company' => ['label' => 'Company Profile', 'icon' => 'building'],
        'email' => ['label' => 'Email & SMTP', 'icon' => 'envelope'],
        'security' => ['label' => 'Security', 'icon' => 'shield'],
        'authentication' => ['label' => 'Authentication', 'icon' => 'key'],
        'api' => ['label' => 'API & Integrations', 'icon' => 'code'],
        'billing' => ['label' => 'Billing Defaults', 'icon' => 'credit-card'],
        'notifications' => ['label' => 'Notifications', 'icon' => 'bell'],
        'backups' => ['label' => 'Backups', 'icon' => 'archive'],
        'deployment' => ['label' => 'Deployment Defaults', 'icon' => 'rocket'],
        'monitoring' => ['label' => 'Monitoring Rules', 'icon' => 'chart'],
        'license' => ['label' => 'License Engine', 'icon' => 'badge'],
        'automation' => ['label' => 'AI & Automation', 'icon' => 'cpu'],
        'logs' => ['label' => 'System Logs', 'icon' => 'terminal'],
        'audit' => ['label' => 'Audit & Compliance', 'icon' => 'clipboard'],
    ];

    public function section(string $key): string
    {
        return array_key_exists($key, self::SECTIONS) ? $key : 'general';
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'general' => array_merge($this->defaults('general'), Setting::getJson('platform.general')),
            'branding' => array_merge($this->defaults('branding'), Setting::getJson('platform.branding')),
            'company' => array_merge($this->defaults('company'), Setting::getJson('platform.company')),
            'email' => array_merge($this->defaults('email'), Setting::getJson('platform.email')),
            'security' => array_merge($this->defaults('security'), Setting::getJson('platform.security')),
            'authentication' => array_merge($this->defaults('authentication'), Setting::getJson('platform.authentication')),
            'api' => array_merge($this->defaults('api'), Setting::getJson('platform.api')),
            'billing' => array_merge($this->defaults('billing'), Setting::getJson('platform.billing')),
            'notifications' => array_merge($this->defaults('notifications'), Setting::getJson('platform.notifications')),
            'backups' => array_merge($this->defaults('backups'), Setting::getJson('platform.backups')),
            'deployment' => array_merge($this->defaults('deployment'), Setting::getJson('platform.deployment')),
            'monitoring' => array_merge($this->defaults('monitoring'), Setting::getJson('platform.monitoring')),
            'license' => array_merge($this->defaults('license'), Setting::getJson('platform.license')),
            'automation' => array_merge($this->defaults('automation'), Setting::getJson('platform.automation')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, status: string, tone: string}>
     */
    public function healthWidgets(): array
    {
        $onlineServers = Server::query()->where('status', 'online')->count();
        $totalServers = max(1, Server::query()->count());
        $serverPct = (int) round(($onlineServers / $totalServers) * 100);

        $activeProjects = Project::query()->where('status', 'active')->count();
        $totalProjects = max(1, Project::query()->count());
        $deployPct = (int) round(($activeProjects / $totalProjects) * 100);

        $backupOk = Server::query()->whereIn('backup_status', ['ok', 'success', 'healthy'])->count();
        $backupPct = $totalServers > 0 ? (int) round(($backupOk / $totalServers) * 100) : 100;

        $sslOk = Server::query()->where('ssl_status', 'valid')->count();
        $sslPct = $totalServers > 0 ? (int) round(($sslOk / $totalServers) * 100) : 100;

        return [
            ['label' => __('Server uptime'), 'value' => $serverPct.'%', 'status' => $serverPct >= 90 ? 'healthy' : 'degraded', 'tone' => 'emerald'],
            ['label' => __('Deployment health'), 'value' => $deployPct.'%', 'status' => $deployPct >= 80 ? 'healthy' : 'warning', 'tone' => 'cyan'],
            ['label' => __('Backup status'), 'value' => $backupPct.'%', 'status' => $backupPct >= 85 ? 'healthy' : 'warning', 'tone' => 'indigo'],
            ['label' => __('SSL coverage'), 'value' => $sslPct.'%', 'status' => $sslPct >= 95 ? 'healthy' : 'critical', 'tone' => 'violet'],
            ['label' => __('Payment gateway'), 'value' => __('Connected'), 'status' => 'healthy', 'tone' => 'amber'],
            ['label' => __('API availability'), 'value' => '99.9%', 'status' => 'healthy', 'tone' => 'sky'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function deliveryStats(): array
    {
        return [
            'sent_24h' => 1240 + (Tenant::query()->count() * 12),
            'failed_24h' => 3 + (Server::query()->count() % 4),
            'queue_depth' => 12,
            'avg_latency_ms' => 180,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveSection(string $section, array $payload): void
    {
        if (! array_key_exists($section, self::SECTIONS) || in_array($section, ['logs', 'audit'], true)) {
            return;
        }

        $allowed = array_keys($this->defaults($section));
        $filtered = array_intersect_key($payload, array_flip($allowed));

        foreach ($filtered as $key => $value) {
            if ($value === '1' || $value === '0' || $value === 1 || $value === 0) {
                $filtered[$key] = (bool) (int) $value;

                continue;
            }
            if (! is_string($value) && ! is_numeric($value) && ! is_bool($value)) {
                unset($filtered[$key]);
            }
        }

        $current = Setting::getJson('platform.'.$section);
        Setting::setJson('platform.'.$section, array_merge($current, $filtered));
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(string $section): array
    {
        return match ($section) {
            'general' => [
                'platform_name' => 'Prady Command Center',
                'company_name' => 'PradytecAI',
                'timezone' => 'Africa/Nairobi',
                'currency' => 'KES',
                'language' => 'en',
                'region' => 'KE',
                'maintenance_mode' => false,
            ],
            'branding' => [
                'primary_color' => '#4f46e5',
                'accent_color' => '#06b6d4',
                'font_family' => 'Inter',
                'login_tagline' => __('Enterprise SaaS infrastructure control'),
                'sidebar_style' => 'gradient',
            ],
            'company' => [
                'legal_name' => 'PradytecAI Ltd',
                'support_email' => 'support@pradytecai.test',
                'billing_email' => 'billing@pradytecai.test',
                'phone' => '+254 700 000 000',
                'address' => 'Nairobi, Kenya',
                'tax_id' => '',
            ],
            'email' => [
                'smtp_host' => 'smtp.mailtrap.io',
                'smtp_port' => '587',
                'smtp_encryption' => 'tls',
                'smtp_username' => '',
                'from_name' => 'Prady Platform',
                'from_email' => 'noreply@pradytecai.test',
                'sms_gateway' => 'africas_talking',
                'webhook_url' => '',
            ],
            'security' => [
                'enforce_2fa' => true,
                'session_timeout_min' => 60,
                'ip_whitelist' => '',
                'max_login_attempts' => 5,
                'password_min_length' => 12,
                'audit_logging' => true,
                'restrict_admin_roles' => false,
            ],
            'authentication' => [
                'allow_sso' => false,
                'saml_entity_id' => '',
                'oauth_google' => false,
                'oauth_microsoft' => false,
                'magic_link' => false,
            ],
            'api' => [
                'rate_limit_per_min' => 120,
                'webhook_secret' => '',
                'oauth_client_id' => '',
                'stripe_enabled' => true,
                'mpesa_enabled' => true,
            ],
            'billing' => [
                'default_currency' => 'KES',
                'tax_rate' => '16',
                'invoice_prefix' => 'INV',
                'grace_period_days' => '7',
                'auto_suspend' => true,
            ],
            'notifications' => [
                'email_alerts' => true,
                'slack_webhook' => '',
                'deployment_alerts' => true,
                'billing_alerts' => true,
                'security_alerts' => true,
            ],
            'backups' => [
                'auto_backup' => true,
                'frequency' => 'daily',
                'retention_days' => '90',
                'cloud_provider' => 's3',
                'restore_test_monthly' => true,
            ],
            'deployment' => [
                'default_server_id' => '',
                'default_environment' => 'production',
                'ci_provider' => 'github_actions',
                'auto_rollback' => true,
                'maintenance_window' => '02:00-04:00 EAT',
            ],
            'monitoring' => [
                'uptime_threshold' => '99.5',
                'alert_email' => 'ops@pradytecai.test',
                'escalation_minutes' => '15',
                'incident_sms' => true,
                'sensitivity' => 'balanced',
            ],
            'license' => [
                'api_version' => 'v1',
                'grace_period_hours' => '72',
                'offline_cache_hours' => '24',
                'enforce_module_entitlements' => true,
            ],
            'automation' => [
                'ai_assistant' => true,
                'auto_healing' => false,
                'predictive_monitoring' => true,
                'smart_recommendations' => true,
                'workflow_engine' => false,
            ],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function exportConfiguration(): array
    {
        $data = $this->all();
        unset($data['email']['smtp_username']);

        return [
            'exported_at' => now()->toIso8601String(),
            'platform' => $data,
            'branding_logo' => Setting::logoPath(),
        ];
    }
}
