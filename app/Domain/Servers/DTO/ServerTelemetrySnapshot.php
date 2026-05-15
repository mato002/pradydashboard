<?php

namespace App\Domain\Servers\DTO;

class ServerTelemetrySnapshot
{
    /**
     * @param  list<string>  $messages
     * @param  list<string>  $sources
     */
    public function __construct(
        public ?string $status = null,
        public ?float $cpuPercent = null,
        public ?float $ramPercent = null,
        public ?float $diskPercent = null,
        public ?int $uptimeSeconds = null,
        public ?string $sslStatus = null,
        public ?string $certificateExpiry = null,
        public ?string $backupStatus = null,
        public array $messages = [],
        public array $sources = [],
    ) {}

    public function merge(self $other): self
    {
        return new self(
            status: $other->status ?? $this->status,
            cpuPercent: $other->cpuPercent ?? $this->cpuPercent,
            ramPercent: $other->ramPercent ?? $this->ramPercent,
            diskPercent: $other->diskPercent ?? $this->diskPercent,
            uptimeSeconds: $other->uptimeSeconds ?? $this->uptimeSeconds,
            sslStatus: $other->sslStatus ?? $this->sslStatus,
            certificateExpiry: $other->certificateExpiry ?? $this->certificateExpiry,
            backupStatus: $other->backupStatus ?? $this->backupStatus,
            messages: array_values(array_unique(array_merge($this->messages, $other->messages))),
            sources: array_values(array_unique(array_merge($this->sources, $other->sources))),
        );
    }

    public function hasMetrics(): bool
    {
        return $this->cpuPercent !== null
            || $this->ramPercent !== null
            || $this->diskPercent !== null
            || $this->status !== null;
    }
}
