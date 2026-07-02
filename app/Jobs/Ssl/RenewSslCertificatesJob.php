<?php

namespace App\Jobs\Ssl;

use App\Domain\Ssl\DomainSslInspector;
use App\Jobs\OperationalJob;
use App\Models\ManagedDomain;
use App\Support\Queue\QueueName;

class RenewSslCertificatesJob extends OperationalJob
{
    public function __construct(
        public ?int $domainId = null,
    ) {
        $this->onQueue(QueueName::LOW);
    }

    public function handle(DomainSslInspector $inspector): void
    {
        $query = ManagedDomain::query()
            ->where('auto_renew', true)
            ->where(function ($q): void {
                $q->whereNull('ssl_expires_at')
                    ->orWhere('ssl_expires_at', '<=', now()->addDays(30));
            });

        if ($this->domainId !== null) {
            $query->whereKey($this->domainId);
        }

        $query->orderBy('ssl_expires_at')->each(function (ManagedDomain $domain) use ($inspector): void {
            $result = $inspector->inspect($domain->domain);
            $history = $domain->renewal_history ?? [];

            $history[] = [
                'checked_at' => now()->toIso8601String(),
                'ssl_status' => $result['ssl_status'],
                'ssl_expires_at' => $result['ssl_expires_at']?->toIso8601String(),
                'message' => $result['message'],
            ];

            $domain->update([
                'ssl_status' => $result['ssl_status'],
                'ssl_expires_at' => $result['ssl_expires_at'] ?? $domain->ssl_expires_at,
                'ssl_issuer' => $result['ssl_issuer'] ?? $domain->ssl_issuer,
                'renewal_history' => array_slice($history, -20),
            ]);
        });
    }
}
