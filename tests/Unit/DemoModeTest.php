<?php

namespace Tests\Unit;

use App\Support\DemoMode;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    public function test_disabled_when_config_flag_is_off(): void
    {
        config(['app.demo_mode' => false]);

        $this->assertFalse(DemoMode::enabled());
    }

    public function test_enabled_in_testing_when_flag_is_on(): void
    {
        config(['app.demo_mode' => true]);
        $this->app['env'] = 'testing';

        $this->assertTrue(DemoMode::enabled());
    }

    public function test_disabled_in_production_even_when_flag_is_on(): void
    {
        config(['app.demo_mode' => true]);
        $this->app['env'] = 'production';

        $this->assertFalse(DemoMode::enabled());
    }

    public function test_disabled_in_staging_even_when_flag_is_on(): void
    {
        config(['app.demo_mode' => true]);
        $this->app['env'] = 'staging';

        $this->assertFalse(DemoMode::enabled());
    }
}
