<?php

namespace App\Jobs\Ssl;

use App\Jobs\OperationalJob;
use App\Models\DnsRecord;
use App\Models\ManagedDomain;
use App\Support\Queue\QueueName;

class VerifyDnsRecordsJob extends OperationalJob
{
    public function __construct(
        public ?int $domainId = null,
    ) {
        $this->onQueue(QueueName::LOW);
    }

    public function handle(): void
    {
        $domains = ManagedDomain::query()
            ->when($this->domainId !== null, fn ($q) => $q->whereKey($this->domainId))
            ->orderBy('domain')
            ->get();

        foreach ($domains as $domain) {
            $this->verifyDomain($domain);
        }
    }

    private function verifyDomain(ManagedDomain $domain): void
    {
        $records = DnsRecord::query()->where('managed_domain_id', $domain->id)->get();
        $failed = 0;

        foreach ($records as $record) {
            $propagated = $this->recordPropagated($domain->domain, $record);
            $record->update([
                'propagation_status' => $propagated ? 'propagated' : 'failed',
            ]);

            if (! $propagated) {
                $failed++;
            }
        }

        $domain->update([
            'dns_status' => $records->isEmpty()
                ? ($this->domainResolvable($domain->domain) ? 'healthy' : 'error')
                : ($failed === 0 ? 'healthy' : ($failed < $records->count() ? 'warning' : 'error')),
            'last_dns_check_at' => now(),
        ]);
    }

    private function recordPropagated(string $domain, DnsRecord $record): bool
    {
        $host = $record->host === '@' ? $domain : $record->host.'.'.$domain;
        $type = strtoupper($record->record_type);

        try {
            $answers = @dns_get_record($host, $this->dnsConstant($type));

            if (! is_array($answers) || $answers === []) {
                return false;
            }

            foreach ($answers as $answer) {
                $candidate = $answer['target'] ?? $answer['ip'] ?? $answer['txt'] ?? $answer['mname'] ?? null;
                if ($candidate !== null && str_contains((string) $candidate, (string) $record->value)) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    private function domainResolvable(string $domain): bool
    {
        return checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA') || checkdnsrr($domain, 'CNAME');
    }

    private function dnsConstant(string $type): int
    {
        return match ($type) {
            'A' => DNS_A,
            'AAAA' => DNS_AAAA,
            'CNAME' => DNS_CNAME,
            'MX' => DNS_MX,
            'TXT' => DNS_TXT,
            'NS' => DNS_NS,
            default => DNS_ANY,
        };
    }
}
