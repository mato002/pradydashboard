<?php

use App\Http\Middleware\AuthenticateDeploymentWebhook;
use App\Http\Middleware\AuthenticatePaymentsGatewayWebhook;
use App\Http\Middleware\AuthenticateProjectApiToken;
use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsurePasswordIsFresh;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\RedirectLegacyNumericPublicIds;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'project.api' => AuthenticateProjectApiToken::class,
            'payments.gateway.webhook' => AuthenticatePaymentsGatewayWebhook::class,
            'deployment.webhook' => AuthenticateDeploymentWebhook::class,
            'permission' => EnsurePermission::class,
            'password.fresh' => EnsurePasswordIsFresh::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureActiveRole::class,
            EnsurePasswordIsFresh::class,
        ]);

        $middleware->prependToGroup('web', RedirectLegacyNumericPublicIds::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $minutes = max(1, (int) config('infrastructure.sync.interval_minutes', 5));

        $schedule->command('servers:sync-telemetry')
            ->cron("*/{$minutes} * * * *")
            ->withoutOverlapping($minutes * 2)
            ->onOneServer();

        $schedule->command('billing:process-recurring')
            ->dailyAt('06:00')
            ->withoutOverlapping(60)
            ->onOneServer();

        $schedule->command('billing:process-overdue')
            ->dailyAt('07:00')
            ->withoutOverlapping(60)
            ->onOneServer();

        $schedule->command('billing:send-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping(60)
            ->onOneServer();

        $schedule->job(new \App\Jobs\Integrations\PollTenantIntegrationsFleetJob())
            ->hourly()
            ->withoutOverlapping(30)
            ->onOneServer();

        $schedule->job(new \App\Jobs\Billing\SyncPaymentsGatewayTransactionsJob())
            ->hourly()
            ->withoutOverlapping(30)
            ->onOneServer();

        $schedule->job(new \App\Jobs\Billing\ReconcilePaymentsBatchJob())
            ->everyTenMinutes()
            ->withoutOverlapping(15)
            ->onOneServer();

        $schedule->job(new \App\Jobs\Ssl\RenewSslCertificatesJob())
            ->dailyAt('03:30')
            ->withoutOverlapping(60)
            ->onOneServer();

        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
        }
    })
    ->create();
