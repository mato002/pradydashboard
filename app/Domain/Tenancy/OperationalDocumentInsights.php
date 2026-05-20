<?php

namespace App\Domain\Tenancy;

use App\Models\OperationalDocument;
use App\Models\Tenant;
use App\Support\OperationalDocumentOptions;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OperationalDocumentInsights
{
    public const EXPIRY_WARNING_DAYS = 30;

    /**
     * @return array{count: int, expiring: int, missing_contracts: int}
     */
    public function summaryForTenant(Tenant $tenant): array
    {
        $tenant->loadMissing(['operationalDocuments', 'projectSubscriptions.project']);

        $documents = $tenant->operationalDocuments;

        return [
            'count' => $documents->count(),
            'expiring' => $this->expiring($documents)->count(),
            'missing_contracts' => $this->missingRequiredContracts($tenant)->count(),
        ];
    }

    /**
     * @param  Collection<int, OperationalDocument>  $documents
     * @return Collection<int, OperationalDocument>
     */
    public function expiring(Collection $documents): Collection
    {
        $threshold = Carbon::now()->addDays(self::EXPIRY_WARNING_DAYS);

        return $documents->filter(function (OperationalDocument $doc): bool {
            if ($doc->status === 'archived' || ! $doc->expiry_date) {
                return false;
            }

            if ($doc->expiry_date->isPast()) {
                return true;
            }

            return $doc->expiry_date->lte(Carbon::now()->addDays(self::EXPIRY_WARNING_DAYS));
        });
    }

    public function isExpiringSoon(OperationalDocument $document): bool
    {
        return $this->expiring(collect([$document]))->isNotEmpty();
    }

    /**
     * @return Collection<int, array{subscription_id: int, project_name: string}>
     */
    public function missingRequiredContracts(Tenant $tenant): Collection
    {
        $signedContracts = $tenant->operationalDocuments
            ->where('status', 'signed')
            ->whereIn('document_type', OperationalDocumentOptions::contractTypes());

        return $tenant->projectSubscriptions
            ->filter(fn ($sub) => (bool) $sub->project?->contract_document_required)
            ->filter(function ($sub) use ($signedContracts): bool {
                return ! $signedContracts->contains(function (OperationalDocument $doc) use ($sub): bool {
                    if ($doc->tenant_project_subscription_id === $sub->id) {
                        return true;
                    }

                    return $doc->project_id === $sub->project_id && $doc->tenant_project_subscription_id === null;
                });
            })
            ->map(fn ($sub) => [
                'subscription_id' => $sub->id,
                'project_name' => $sub->project?->name ?? __('Unknown project'),
            ])
            ->values();
    }
}
