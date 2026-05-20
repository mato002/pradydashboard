<?php

namespace App\Support;

final class DemoMode
{
    public static function enabled(): bool
    {
        return (bool) config('app.demo_mode', false);
    }
}
