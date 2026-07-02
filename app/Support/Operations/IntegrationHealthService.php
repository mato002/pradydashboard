<?php

namespace App\Support\Operations;

use App\Models\HostedProject;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\Cache\OperationalCache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class IntegrationHealthService
{
    public function __construct(
        private readonly OperationalCache $operationalCache,
        private readonly PaymentsGatewayClient $paymentsGateway,
    ) {}

    /**
     * @return list<array{key: string, label: string, status: string, message: string, blocking: bool}>
     */
    public function checks(): array
    {
        return [
            $this->redisCheck(),
            $this->queueCheck(),
            $this->paymentsGatewayCheck(),
            $this->infrastructureCheck(),
            $this->mailCheck(),
            $this->tenantIntegrationsCheck(),
        ];
    }

    public function hasBlockingIssues(): bool
    {
        foreach ($this->checks() as $check) {
            if ($check['blocking'] && $check['status'] === 'fail') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{key: string, label: string, status: string, message: string, blocking: bool}>
     */
    public function blockingChecks(): array
    {
        return array_values(array_filter(
            $this->checks(),
            fn (array $check) => $check['blocking'] && $check['status'] === 'fail',
        ));
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function redisCheck(): array
    {
        $usesRedis = in_array(config('cache.default'), ['redis'], true)
            || config('session.driver') === 'redis'
            || config('queue.default') === 'redis';

        if (! $usesRedis) {
            return [
                'key' => 'redis',
                'label' => __('Redis'),
                'status' => 'warn',
                'message' => __('Not required — using :cache cache, :session sessions, :queue queues.', [
                    'cache' => config('cache.default'),
                    'session' => config('session.driver'),
                    'queue' => config('queue.default'),
                ]),
                'blocking' => false,
            ];
        }

        try {
            $pong = Redis::connection()->ping();
            $ok = is_bool($pong) ? $pong : strtoupper((string) $pong) === 'PONG' || strtoupper((string) $pong) === '+PONG';

            return [
                'key' => 'redis',
                'label' => __('Redis'),
                'status' => $ok ? 'pass' : 'fail',
                'message' => $ok
                    ? __('Connected (:host).', ['host' => config('database.redis.default.host')])
                    : __('Redis is configured but unreachable. Start Redis or switch to database drivers in .env.'),
                'blocking' => true,
            ];
        } catch (\Throwable $e) {
            return [
                'key' => 'redis',
                'label' => __('Redis'),
                'status' => 'fail',
                'message' => __('Redis connection failed: :error', ['error' => $e->getMessage()]),
                'blocking' => true,
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function queueCheck(): array
    {
        if (config('queue.default') === 'sync') {
            return [
                'key' => 'queue',
                'label' => __('Queue workers'),
                'status' => 'warn',
                'message' => __('QUEUE_CONNECTION=sync — jobs run inline. Use redis + queue:work in production.'),
                'blocking' => false,
            ];
        }

        try {
            Queue::connection()->size();

            return [
                'key' => 'queue',
                'label' => __('Queue workers'),
                'status' => 'warn',
                'message' => __('Queue connection OK. Ensure a worker is running (composer dev or php artisan queue:work).'),
                'blocking' => false,
            ];
        } catch (\Throwable $e) {
            return [
                'key' => 'queue',
                'label' => __('Queue workers'),
                'status' => 'fail',
                'message' => __('Queue connection failed: :error', ['error' => $e->getMessage()]),
                'blocking' => true,
            ];
        }
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function paymentsGatewayCheck(): array
    {
        if (! filled(config('payment_gateway.admin_token'))) {
            return [
                'key' => 'payments_gateway',
                'label' => __('Payments Gateway'),
                'status' => 'fail',
                'message' => __('Set PAYMENTS_GATEWAY_ADMIN_TOKEN in .env to enable the control plane.'),
                'blocking' => true,
            ];
        }

        if (! $this->paymentsGateway->isConfigured()) {
            return [
                'key' => 'payments_gateway',
                'label' => __('Payments Gateway'),
                'status' => 'fail',
                'message' => __('Gateway client is not configured.'),
                'blocking' => true,
            ];
        }

        $health = $this->paymentsGateway->health();

        if ($health['unavailable'] ?? false) {
            return [
                'key' => 'payments_gateway',
                'label' => __('Payments Gateway'),
                'status' => 'fail',
                'message' => $health['error'] ?? __('Gateway at :url is unreachable.', [
                    'url' => config('payment_gateway.base_url'),
                ]),
                'blocking' => true,
            ];
        }

        return [
            'key' => 'payments_gateway',
            'label' => __('Payments Gateway'),
            'status' => ($health['ok'] ?? false) ? 'pass' : 'warn',
            'message' => ($health['ok'] ?? false)
                ? __('Connected to :url.', ['url' => config('payment_gateway.base_url')])
                : ($health['message'] ?? __('Gateway responded with HTTP :status.', ['status' => $health['status'] ?? 0])),
            'blocking' => false,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function infrastructureCheck(): array
    {
        $fleetTokens = array_filter([
            'WHM' => config('infrastructure.whm.api_token'),
            'DigitalOcean' => config('infrastructure.digitalocean.token'),
            'Hetzner' => config('infrastructure.hetzner.token'),
        ]);

        $serversNeedingConfig = Server::query()
            ->where('telemetry_mode', '!=', 'manual')
            ->where(function ($q): void {
                $q->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', now()->subHours(6));
            })
            ->count();

        if ($fleetTokens === [] && $serversNeedingConfig > 0) {
            return [
                'key' => 'infrastructure',
                'label' => __('Infrastructure telemetry'),
                'status' => 'warn',
                'message' => __(':count server(s) need per-server credentials or fleet tokens (INFRA_*_TOKEN).', [
                    'count' => $serversNeedingConfig,
                ]),
                'blocking' => false,
            ];
        }

        if ($fleetTokens !== []) {
            return [
                'key' => 'infrastructure',
                'label' => __('Infrastructure telemetry'),
                'status' => 'pass',
                'message' => __('Fleet tokens configured for: :providers.', [
                    'providers' => implode(', ', array_keys($fleetTokens)),
                ]),
                'blocking' => false,
            ];
        }

        return [
            'key' => 'infrastructure',
            'label' => __('Infrastructure telemetry'),
            'status' => 'pass',
            'message' => __('All servers on manual telemetry or recently synced.'),
            'blocking' => false,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function mailCheck(): array
    {
        if (config('mail.default') === 'log') {
            return [
                'key' => 'mail',
                'label' => __('Outbound email'),
                'status' => 'warn',
                'message' => __('MAIL_MAILER=log — billing reminders will not reach tenants. Configure SMTP for production.'),
                'blocking' => false,
            ];
        }

        return [
            'key' => 'mail',
            'label' => __('Outbound email'),
            'status' => 'pass',
            'message' => __('Mail driver: :driver.', ['driver' => config('mail.default')]),
            'blocking' => false,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, blocking: bool}
     */
    private function tenantIntegrationsCheck(): array
    {
        $projectsMissingToken = HostedProject::query()->whereNull('api_token')->orWhere('api_token', '')->count();
        $integrationsFailing = TenantProjectServiceIntegration::query()
            ->tenantSystem()
            ->where('status', 'failing')
            ->count();
        $tenantsUnlinked = Tenant::query()
            ->whereNull('payments_gateway_tenant_uuid')
            ->where('status', 'active')
            ->count();

        $issues = [];
        if ($projectsMissingToken > 0) {
            $issues[] = __(':count hosted project(s) missing API tokens', ['count' => $projectsMissingToken]);
        }
        if ($integrationsFailing > 0) {
            $issues[] = __(':count tenant system integration(s) failing', ['count' => $integrationsFailing]);
        }
        if ($tenantsUnlinked > 0 && filled(config('payment_gateway.admin_token'))) {
            $issues[] = __(':count active tenant(s) not linked to Payments Gateway', ['count' => $tenantsUnlinked]);
        }

        if ($issues === []) {
            return [
                'key' => 'tenant_integrations',
                'label' => __('Tenant integrations'),
                'status' => 'pass',
                'message' => __('Hosted projects and tenant system APIs look healthy.'),
                'blocking' => false,
            ];
        }

        return [
            'key' => 'tenant_integrations',
            'label' => __('Tenant integrations'),
            'status' => 'warn',
            'message' => implode('; ', $issues).'.',
            'blocking' => false,
        ];
    }
}
