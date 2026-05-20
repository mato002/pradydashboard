<?php

namespace App\Domain\Tenancy;

use App\Models\Project;
use App\Support\OperationalDocumentOptions;
use Illuminate\Support\Collection;

class ProjectOperationalInsights
{
    public function __construct(
        private readonly OperationalDocumentInsights $documentInsights,
    ) {}

    /**
     * @return Collection<int, array{tenant_id: int, company_name: string, subscription_id: int}>
     */
    public function tenantsMissingRequiredContracts(Project $project): Collection
    {
        $project->loadMissing([
            'tenantProjectSubscriptions.tenant.operationalDocuments',
            'tenantProjectSubscriptions.project',
        ]);

        return $project->tenantProjectSubscriptions
            ->filter(fn ($sub) => (bool) $project->contract_document_required)
            ->filter(function ($sub) {
                $tenant = $sub->tenant;
                if (! $tenant) {
                    return true;
                }

                $signed = $tenant->operationalDocuments
                    ->where('status', 'signed')
                    ->whereIn('document_type', OperationalDocumentOptions::contractTypes());

                $hasContract = $signed->contains(function ($doc) use ($sub): bool {
                    return $doc->tenant_project_subscription_id === $sub->id
                        || ($doc->project_id === $sub->project_id && $doc->tenant_project_subscription_id === null);
                });

                return ! $hasContract;
            })
            ->map(fn ($sub) => [
                'tenant_id' => $sub->tenant_id,
                'company_name' => $sub->tenant?->company_name ?? __('Unknown'),
                'subscription_id' => $sub->id,
            ])
            ->values();
    }

    /**
     * @return array<string, int>
     */
    public function integrationsByServiceType(Project $project): array
    {
        $project->loadMissing('tenantProjectSubscriptions.serviceIntegrations');

        $counts = [];
        foreach ($project->tenantProjectSubscriptions as $subscription) {
            foreach ($subscription->serviceIntegrations as $integration) {
                $type = $integration->service_type;
                $counts[$type] = ($counts[$type] ?? 0) + 1;
            }
        }

        ksort($counts);

        return $counts;
    }
}
