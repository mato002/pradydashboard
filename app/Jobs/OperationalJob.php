<?php

namespace App\Jobs;

use App\Jobs\Concerns\UsesOperationalLocks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class OperationalJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UsesOperationalLocks;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public int $timeout = 120;

    public function failed(?Throwable $exception): void
    {
        Log::error('Operational queue job failed.', [
            'job' => static::class,
            'queue' => $this->queue,
            'attempts' => $this->attempts(),
            'error' => $exception?->getMessage(),
        ]);
    }
}
