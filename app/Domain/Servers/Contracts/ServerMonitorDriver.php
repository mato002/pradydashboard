<?php

namespace App\Domain\Servers\Contracts;

use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Models\Server;

interface ServerMonitorDriver
{
    public function key(): string;

    public function supports(Server $server): bool;

    public function poll(Server $server): ServerTelemetrySnapshot;
}
