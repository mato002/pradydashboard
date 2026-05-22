<?php

use App\Http\Middleware\AuthenticateProjectApiToken;
use App\Http\Middleware\EnsurePasswordIsFresh;
use App\Http\Middleware\EnsurePermission;
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
            'permission' => EnsurePermission::class,
            'password.fresh' => EnsurePasswordIsFresh::class,
        ]);

        $middleware->appendToGroup('web', EnsurePasswordIsFresh::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $minutes = max(1, (int) config('infrastructure.sync.interval_minutes', 5));
        $schedule->command('servers:sync-telemetry')->cron("*/{$minutes} * * * *");
        $schedule->command('billing:process-recurring')->dailyAt('06:00');
        $schedule->command('billing:process-overdue')->dailyAt('07:00');
        $schedule->command('billing:send-reminders')->dailyAt('08:00');
    })
    ->create();
