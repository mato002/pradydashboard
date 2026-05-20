<?php

namespace App\Http\Controllers\Concerns;

use App\Domain\Activity\ActivityLogger;

trait LogsOperationalActivity
{
    protected function activityLogger(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}
