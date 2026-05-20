<?php

namespace App\Domain\Servers\DTO;

class ServerTelemetrySnapshot
{
    /**
     * @param  list<string>  $messages
     * @param  list<string>  $sources
     * @param  array<string, bool|string|null>  $healthChecks
     */
    public function __construct(
        public ?string $status = null,
        public ?float $cpuPercent = null,
        public ?float $ramPercent = null,
        public ?float $diskPercent = null,
        public ?float $loadAverage = null,
        public ?int $uptimeSeconds = null,
        public ?string $sslStatus = null,
        public ?string $certificateExpiry = null,
        public ?int $sslDaysRemaining = null,
        public ?string $backupStatus = null,
        public ?int $accountCount = null,
        public array $messages = [],
        public array $sources = [],
        public array $healthChecks = [],
    ) {}

    public function merge(self $other): self
    {
        return new self(
            status: $other->status ?? $this->status,
            cpuPercent: $other->cpuPercent ?? $this->cpuPercent,
            ramPercent: $other->ramPercent ?? $this->ramPercent,
            diskPercent: $other->diskPercent ?? $this->diskPercent,
            loadAverage: $other->loadAverage ?? $this->loadAverage,
            uptimeSeconds: $other->uptimeSeconds ?? $this->uptimeSeconds,
            sslStatus: $other->sslStatus ?? $this->sslStatus,
            certificateExpiry: $other->certificateExpiry ?? $this->certificateExpiry,
            sslDaysRemaining: $other->sslDaysRemaining ?? $this->sslDaysRemaining,
            backupStatus: $other->backupStatus ?? $this->backupStatus,
            accountCount: $other->accountCount ?? $this->accountCount,
            messages: array_values(array_unique(array_merge($this->messages, $other->messages))),
            sources: array_values(array_unique(array_merge($this->sources, $other->sources))),
            healthChecks: array_merge($this->healthChecks, $other->healthChecks),
        );
    }

    public function hasMetrics(): bool
    {
        return $this->cpuPercent !== null
            || $this->ramPercent !== null
            || $this->diskPercent !== null
            || $this->loadAverage !== null
            || $this->status !== null;
    }

    public function hasWhmMetrics(): bool
    {
        return in_array('whm', $this->sources, true)
            && ($this->cpuPercent !== null || $this->ramPercent !== null || $this->diskPercent !== null);
    }
}
