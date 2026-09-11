<?php

namespace App\Support;

final class DemoMode
{
    /**
     * Demo auto-seeding is allowed only when APP_DEMO_MODE is on and the app
     * environment is explicitly non-production (local / demo / testing).
     * Production (and staging) never auto-seed, even if APP_DEMO_MODE was left true.
     */
    public static function enabled(): bool
    {
        if (! (bool) config('app.demo_mode', false)) {
            return false;
        }

        return app()->environment(self::allowedEnvironments());
    }

    /**
     * @return list<string>
     */
    public static function allowedEnvironments(): array
    {
        return ['local', 'demo', 'testing'];
    }
}
