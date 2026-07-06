<?php

namespace App\Support\PublicId;

use App\Models\Concerns\HasPublicId;
use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\PublicId\PublicIdFormat;
use Illuminate\Database\Eloquent\Model;

class PublicIdAuditor
{
    /**
     * @var array<string, class-string<Model&HasPublicId>>
     */
    private const RESOURCES = [
        'Tenants' => Tenant::class,
        'Invoices' => TenantInvoice::class,
        'Hosted Projects' => HostedProject::class,
        'Servers' => Server::class,
        'Payments' => TenantPayment::class,
        'Support Tickets' => SupportTicket::class,
        'Operational Documents' => OperationalDocument::class,
    ];

    /**
     * @return list<array{resource: string, count: int, missing: int, duplicate: int, invalid_format: int, status: string}>
     */
    public function audit(): array
    {
        $rows = [];

        foreach (self::RESOURCES as $label => $modelClass) {
            $rows[] = $this->auditModel($label, $modelClass);
        }

        return $rows;
    }

    /**
     * @param  class-string<Model&HasPublicId>  $modelClass
     * @return array{resource: string, count: int, missing: int, duplicate: int, invalid_format: int, status: string}
     */
    private function auditModel(string $label, string $modelClass): array
    {
        $records = $modelClass::query()->pluck('public_id');
        $count = $records->count();
        $missing = $records->filter(fn (?string $id) => blank($id))->count();

        $filled = $records->filter(fn (?string $id) => filled($id));
        $duplicate = $filled->count() - $filled->unique()->count();
        $invalidFormat = $filled->filter(fn (string $id) => ! $this->isValidFormat($id))->count();

        $issues = $missing + $duplicate + $invalidFormat;

        return [
            'resource' => $label,
            'count' => $count,
            'missing' => $missing,
            'duplicate' => $duplicate,
            'invalid_format' => $invalidFormat,
            'status' => $issues === 0 ? 'OK' : 'ISSUES',
        ];
    }

    public function isValidFormat(string $publicId): bool
    {
        return PublicIdFormat::isValid($publicId);
    }

    public function hasIssues(): bool
    {
        foreach ($this->audit() as $row) {
            if ($row['status'] !== 'OK') {
                return true;
            }
        }

        return false;
    }

    /**
     * Safely backfill only missing public_ids. Never overwrites existing values.
     *
     * @return array{repaired: int, skipped: int}
     */
    public function repairMissing(): array
    {
        $repaired = 0;
        $skipped = 0;

        foreach (self::RESOURCES as $modelClass) {
            $modelClass::query()
                ->where(function ($query): void {
                    $query->whereNull('public_id')->orWhere('public_id', '');
                })
                ->orderBy('id')
                ->each(function (Model $model) use (&$repaired, &$skipped, $modelClass): void {
                    if (filled($model->public_id)) {
                        $skipped++;

                        return;
                    }

                    $modelClass::query()
                        ->whereKey($model->getKey())
                        ->update(['public_id' => $modelClass::generateUniquePublicId()]);

                    $repaired++;
                });
        }

        return compact('repaired', 'skipped');
    }

    /**
     * @return list<array{resource: string, table: string}>
     */
    public function registeredResources(): array
    {
        return collect(self::RESOURCES)
            ->map(fn (string $modelClass, string $label) => [
                'resource' => $label,
                'table' => (new $modelClass)->getTable(),
            ])
            ->values()
            ->all();
    }
}
