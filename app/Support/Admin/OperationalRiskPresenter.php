<?php

namespace App\Support\Admin;

use App\Support\OperationalRiskCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * View-layer grouping and summaries for operational risks (no scan logic changes).
 */
class OperationalRiskPresenter
{
    /** @var list<string> */
    private const AGGREGATABLE_PREFIXES = [
        'subscription_renewal',
        'tenant_renewal',
        'invoice_overdue',
        'telemetry_stale',
        'telemetry_manual',
        'whm_token_missing',
        'tenant_ssl',
        'server_ssl',
        'integration_failed',
        'deployment_outdated',
        'document_expiring',
        'follow_up_overdue',
        'ticket_overdue',
        'missing_contract',
        'server_renewal',
        'provider_notice',
    ];

    /** @var array<string, array{label: string, categories: list<string>, empty: string}> */
    private const SECTIONS = [
        'infrastructure' => [
            'label' => 'Infrastructure',
            'categories' => [
                OperationalRiskCategory::INFRASTRUCTURE,
                OperationalRiskCategory::SSL,
                OperationalRiskCategory::SERVER,
                OperationalRiskCategory::DEPLOYMENT,
            ],
            'empty' => 'No infrastructure risks',
        ],
        'billing' => [
            'label' => 'Billing & Collections',
            'categories' => [OperationalRiskCategory::BILLING],
            'empty' => 'No billing risks',
        ],
        'licensing' => [
            'label' => 'Subscriptions & Licensing',
            'categories' => [
                OperationalRiskCategory::RENEWAL,
                OperationalRiskCategory::CONTRACT,
            ],
            'empty' => 'No licensing or subscription risks',
        ],
        'support' => [
            'label' => 'Support & Operations',
            'categories' => [
                OperationalRiskCategory::SUPPORT,
                OperationalRiskCategory::INTEGRATION,
                OperationalRiskCategory::DOCUMENT,
                OperationalRiskCategory::HR,
            ],
            'empty' => 'No support or operations risks',
        ],
    ];

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     * @return array{
     *     total: int,
     *     summary: array<string, array{count: int, label: string, tone: string}>,
     *     sections: list<array{
     *         id: string,
     *         label: string,
     *         count: int,
     *         empty: string,
     *         items: list<array<string, mixed>>
     *     }>
     * }
     */
    public static function build(Collection $risks): array
    {
        $presenter = new self;

        return [
            'total' => $risks->count(),
            'summary' => $presenter->summary($risks),
            'sections' => $presenter->sections($risks),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     * @return array<string, array{count: int, label: string, tone: string}>
     */
    private function summary(Collection $risks): array
    {
        $critical = $risks->where('severity', 'critical')->count();
        $high = $risks->where('severity', 'warning')->count();
        $medium = $risks->where('severity', 'info')->count();

        $infrastructure = $this->countInSection($risks, 'infrastructure');
        $billing = $this->countInSection($risks, 'billing');
        $licensing = $this->countInSection($risks, 'licensing');
        $support = $this->countInSection($risks, 'support');

        return [
            'critical' => [
                'count' => $critical,
                'label' => $critical > 0 ? __('Needs action') : __('Clear'),
                'tone' => 'rose',
            ],
            'high' => [
                'count' => $high,
                'label' => $high > 0 ? __('Active') : __('Clear'),
                'tone' => 'amber',
            ],
            'medium' => [
                'count' => $medium,
                'label' => $medium > 0 ? __('Monitor') : __('Clear'),
                'tone' => 'yellow',
            ],
            'infrastructure' => [
                'count' => $infrastructure,
                'label' => $infrastructure > 0 ? __('Infra') : __('Stable'),
                'tone' => 'sky',
            ],
            'billing' => [
                'count' => $billing,
                'label' => $billing > 0 ? __('Collections') : __('Stable'),
                'tone' => 'violet',
            ],
            'licensing' => [
                'count' => $licensing,
                'label' => $licensing > 0 ? __('Renewals') : __('Stable'),
                'tone' => 'indigo',
            ],
            'support' => [
                'count' => $support,
                'label' => $support > 0 ? __('Escalations') : __('Stable'),
                'tone' => 'slate',
            ],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     */
    private function countInSection(Collection $risks, string $sectionId): int
    {
        $categories = self::SECTIONS[$sectionId]['categories'] ?? [];

        return $risks->whereIn('category', $categories)->count();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     * @return list<array{id: string, label: string, count: int, empty: string, items: list<array<string, mixed>>}>
     */
    private function sections(Collection $risks): array
    {
        $out = [];

        foreach (self::SECTIONS as $id => $meta) {
            $sectionRisks = $risks
                ->whereIn('category', $meta['categories'])
                ->values();

            $out[] = [
                'id' => $id,
                'label' => __($meta['label']),
                'count' => $sectionRisks->count(),
                'empty' => __($meta['empty']),
                'items' => $this->itemsForSection($sectionRisks),
            ];
        }

        return $out;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     * @return list<array<string, mixed>>
     */
    private function itemsForSection(Collection $risks): array
    {
        $items = [];
        $byPrefix = $risks->groupBy(fn (array $r): string => Str::before((string) $r['key'], ':'));

        foreach ($byPrefix as $prefix => $group) {
            $group = $group->values();
            if ($group->count() >= 2 && in_array($prefix, self::AGGREGATABLE_PREFIXES, true)) {
                $items[] = $this->bundleItem($prefix, $group->all());

                continue;
            }

            foreach ($group as $risk) {
                $items[] = $this->singleItem($risk);
            }
        }

        usort($items, fn (array $a, array $b): int => $this->severityRank($a['severity']) <=> $this->severityRank($b['severity']));

        return $items;
    }

    /**
     * @param  list<array<string, mixed>>  $risks
     * @return array<string, mixed>
     */
    private function bundleItem(string $prefix, array $risks): array
    {
        $worst = collect($risks)->sortBy(fn (array $r) => $this->severityRank($r['severity']))->first();
        $count = count($risks);

        return [
            'type' => 'bundle',
            'prefix' => $prefix,
            'severity' => $worst['severity'] ?? 'info',
            'severity_label' => $this->severityLabel($worst['severity'] ?? 'info'),
            'title' => $this->bundleTitle($prefix, $count),
            'subtitle' => $this->bundleSubtitle($prefix, $risks),
            'time_label' => $this->bundleTimeLabel($risks),
            'risks' => array_map(fn (array $r) => $this->enrichRisk($r), $risks),
        ];
    }

    /**
     * @param  array<string, mixed>  $risk
     * @return array<string, mixed>
     */
    private function singleItem(array $risk): array
    {
        $enriched = $this->enrichRisk($risk);

        return [
            'type' => 'single',
            'severity' => $enriched['severity'],
            'severity_label' => $enriched['severity_label'],
            'risk' => $enriched,
        ];
    }

    /**
     * @param  array<string, mixed>  $risk
     * @return array<string, mixed>
     */
    private function enrichRisk(array $risk): array
    {
        $risk['severity_label'] = $this->severityLabel($risk['severity'] ?? 'info');
        $risk['entity_label'] = $this->entityLabel($risk);
        $risk['time_label'] = $this->timeLabel($risk);
        $risk['actions'] = $this->actionsFor($risk);

        return $risk;
    }

    private function severityLabel(string $severity): string
    {
        return match ($severity) {
            'critical' => __('Critical'),
            'warning' => __('High'),
            'info' => __('Info'),
            default => __('Medium'),
        };
    }

    private function severityRank(string $severity): int
    {
        return match ($severity) {
            'critical' => 0,
            'warning' => 1,
            'info' => 2,
            default => 3,
        };
    }

    private function bundleTitle(string $prefix, int $count): string
    {
        return match ($prefix) {
            'subscription_renewal' => $count === 1
                ? __('1 subscription requires renewal')
                : __(':count subscriptions require renewal', ['count' => $count]),
            'tenant_renewal' => $count === 1
                ? __('1 tenant renewal due')
                : __(':count tenant renewals due', ['count' => $count]),
            'invoice_overdue' => $count === 1
                ? __('1 overdue invoice')
                : __(':count overdue invoices', ['count' => $count]),
            'telemetry_stale' => $count === 1
                ? __('1 server not syncing')
                : __(':count servers not syncing', ['count' => $count]),
            'telemetry_manual' => $count === 1
                ? __('1 server on manual telemetry')
                : __(':count servers on manual telemetry', ['count' => $count]),
            'whm_token_missing' => $count === 1
                ? __('1 server missing WHM token')
                : __(':count servers missing WHM token', ['count' => $count]),
            'tenant_ssl', 'server_ssl' => $count === 1
                ? __('1 SSL certificate expiring')
                : __(':count SSL certificates expiring', ['count' => $count]),
            'integration_failed' => $count === 1
                ? __('1 failed integration')
                : __(':count failed integrations', ['count' => $count]),
            'deployment_outdated' => $count === 1
                ? __('1 outdated deployment')
                : __(':count outdated deployments', ['count' => $count]),
            'document_expiring' => $count === 1
                ? __('1 document expiring')
                : __(':count documents expiring', ['count' => $count]),
            'follow_up_overdue' => $count === 1
                ? __('1 overdue follow-up')
                : __(':count overdue follow-ups', ['count' => $count]),
            'ticket_overdue' => $count === 1
                ? __('1 overdue ticket')
                : __(':count overdue tickets', ['count' => $count]),
            'missing_contract' => $count === 1
                ? __('1 missing contract')
                : __(':count missing contracts', ['count' => $count]),
            'server_renewal' => $count === 1
                ? __('1 server renewal due')
                : __(':count server renewals due', ['count' => $count]),
            'provider_notice' => $count === 1
                ? __('1 provider notice')
                : __(':count provider notices', ['count' => $count]),
            default => __(':count related risks', ['count' => $count]),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $risks
     */
    private function bundleSubtitle(string $prefix, array $risks): string
    {
        $sample = $risks[0] ?? [];

        return match ($prefix) {
            'subscription_renewal', 'tenant_renewal', 'invoice_overdue', 'missing_contract' => __('Review affected tenants and billing'),
            'telemetry_stale', 'telemetry_manual', 'whm_token_missing', 'server_renewal', 'provider_notice' => __('Fleet and server health'),
            'tenant_ssl', 'server_ssl' => __('Certificate renewal window'),
            'integration_failed', 'deployment_outdated' => __('Tenant deployments and integrations'),
            'follow_up_overdue', 'ticket_overdue' => __('Support queue attention'),
            default => (string) ($sample['recommended_action'] ?? __('Review items')),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $risks
     */
    private function bundleTimeLabel(array $risks): ?string
    {
        $dueDates = collect($risks)
            ->pluck('due_at')
            ->filter()
            ->map(fn ($d) => $d instanceof Carbon ? $d : Carbon::parse($d));

        if ($dueDates->isEmpty()) {
            $syncDates = collect($risks)
                ->filter(fn (array $r) => str_starts_with((string) $r['key'], 'telemetry_'))
                ->map(fn (array $r) => $r['due_at'])
                ->filter();

            if ($syncDates->isNotEmpty()) {
                $latest = $syncDates->sort()->first();

                return $latest instanceof Carbon
                    ? __('Last sync :time', ['time' => $latest->diffForHumans()])
                    : null;
            }

            return null;
        }

        $soonest = $dueDates->sort()->first();

        if ($soonest->isPast()) {
            return __(':time overdue', ['time' => $soonest->diffForHumans(null, true)]);
        }

        return __('Next due :time', ['time' => $soonest->diffForHumans()]);
    }

    /**
     * @param  array<string, mixed>  $risk
     */
    private function entityLabel(array $risk): ?string
    {
        if (preg_match('/:tenant\s·\s:project|for\s+:tenant|:name\srenews/', (string) $risk['description'])) {
            return null;
        }

        $desc = (string) ($risk['description'] ?? '');
        if (preg_match('/^([^·—]+)/', $desc, $m)) {
            $candidate = trim($m[1]);
            if ($candidate !== '' && ! str_contains(strtolower($candidate), 'owes')) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $risk
     */
    private function timeLabel(array $risk): ?string
    {
        $due = $risk['due_at'] ?? null;
        if ($due instanceof Carbon) {
            if ($due->isPast()) {
                return __(':time overdue', ['time' => $due->diffForHumans(null, true)]);
            }

            return __('Due :time', ['time' => $due->diffForHumans()]);
        }

        if (str_contains((string) $risk['key'], 'telemetry_stale') && preg_match('/last synced\s+(.+)$/i', (string) $risk['description'], $m)) {
            return __('Last sync :time', ['time' => trim($m[1])]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $risk
     * @return list<array{id: string, label: string, href?: string, method?: string, confirm?: string}>
     */
    private function actionsFor(array $risk): array
    {
        $actions = [];

        if (! empty($risk['url'])) {
            $actions[] = ['id' => 'view', 'label' => __('View'), 'href' => $risk['url']];
        }

        if (! empty($risk['tenant_id'])) {
            $actions[] = [
                'id' => 'tenant',
                'label' => __('Open tenant'),
                'href' => route('tenants.show', $risk['tenant_id']),
            ];
        }

        if (str_starts_with((string) $risk['key'], 'invoice_overdue:') && ! empty($risk['url'])) {
            $actions[] = ['id' => 'invoice', 'label' => __('Open invoice'), 'href' => $risk['url']];
        } elseif (str_starts_with((string) $risk['key'], 'invoice_overdue:')) {
            $actions[] = [
                'id' => 'collections',
                'label' => __('Open collections'),
                'href' => route('invoices.index', ['status' => 'overdue']),
            ];
        }

        if (! empty($risk['server_id'])) {
            $actions[] = [
                'id' => 'server',
                'label' => __('Open server'),
                'href' => route('servers.show', $risk['server_id']),
            ];
        }

        if (str_starts_with((string) $risk['key'], 'telemetry_stale:') && ! empty($risk['server_id'])) {
            $actions[] = [
                'id' => 'sync',
                'label' => __('Retry sync'),
                'href' => route('servers.sync-telemetry', $risk['server_id']),
                'method' => 'POST',
            ];
        }

        if (! ($risk['acknowledged'] ?? false)) {
            $actions[] = [
                'id' => 'dismiss',
                'label' => __('Dismiss'),
                'type' => 'acknowledge',
                'risk_key' => $risk['key'],
            ];
        }

        return $actions;
    }
}
