<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayGoLiveDryRunController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $paybillAccountUuid = trim((string) $request->query('paybill_account_uuid', ''));
        $skipOAuth = $request->boolean('skip_oauth');
        $strict = $request->boolean('strict');
        $ranDryRun = filled($paybillAccountUuid) && $request->has('run');

        $report = null;
        $groupedChecklist = $this->emptyChecklistGroups();
        $gatewayUnavailable = false;
        $responseTimeMs = 0;

        if ($ranDryRun) {
            $params = array_filter([
                'skip_oauth' => $request->has('skip_oauth') ? ($skipOAuth ? '1' : '0') : null,
                'strict' => $request->has('strict') ? ($strict ? '1' : '0') : null,
            ], fn (mixed $value) => $value !== null);

            $response = $client->getGoLiveDryRun($paybillAccountUuid, $params);
            $responseTimeMs = (int) ($response['response_time_ms'] ?? 0);
            $gatewayUnavailable = (bool) ($response['unavailable'] ?? false) || ! ($response['ok'] ?? false);
            $report = $gatewayUnavailable ? null : $client->extractData($response);
            $report = is_array($report) ? $report : null;

            if ($report !== null) {
                $groupedChecklist = $this->groupChecklistItems($report['checklist_items'] ?? []);
            }
        }

        return view('settings.integrations.payments-gateway.go-live-dry-run.index', [
            'filters' => [
                'paybill_account_uuid' => $paybillAccountUuid,
                'skip_oauth' => $skipOAuth,
                'strict' => $strict,
            ],
            'ranDryRun' => $ranDryRun,
            'contextualLaunch' => $ranDryRun && filled($paybillAccountUuid),
            'report' => $report,
            'groupedChecklist' => $groupedChecklist,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? __('Go-live dry run could not be completed because payments.pradytecai.com is unavailable.')
                : null,
            'responseTimeMs' => $responseTimeMs,
        ]);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function emptyChecklistGroups(): array
    {
        return [
            'environment' => [],
            'daraja' => [],
            'callbacks' => [],
            'queue' => [],
            'workers' => [],
            'security' => [],
            'treasury' => [],
            'webhooks' => [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, list<array<string, mixed>>>
     */
    protected function groupChecklistItems(array $items): array
    {
        $groups = $this->emptyChecklistGroups();

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $item = $this->enrichChecklistItem($item);
            $category = (string) ($item['category'] ?? 'environment');
            $key = (string) ($item['key'] ?? '');

            if (in_array($category, ['environment', 'readiness', 'account'], true)) {
                $groups['environment'][] = $item;
            } elseif ($category === 'daraja') {
                $groups['daraja'][] = $item;
            } elseif ($category === 'callbacks') {
                $groups['callbacks'][] = $item;
            } elseif ($category === 'webhooks' || $key === 'stale_failed_webhooks') {
                $groups['webhooks'][] = $item;
            } elseif ($category === 'treasury') {
                $groups['treasury'][] = $item;
            } elseif ($category === 'operations') {
                if ($key === 'pending_dead_letters') {
                    $groups['queue'][] = $item;
                } else {
                    $groups['security'][] = $item;
                }
            } elseif ($category === 'queue') {
                if (str_contains($key, 'worker')) {
                    $groups['workers'][] = $item;
                } else {
                    $groups['queue'][] = $item;
                }
            }
        }

        return $groups;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function enrichChecklistItem(array $item): array
    {
        $status = strtolower((string) ($item['status'] ?? 'unknown'));
        $blocking = (bool) ($item['blocking'] ?? false);

        $severity = match (true) {
            $status === 'fail' || $blocking => 'block',
            $status === 'warn' => 'warn',
            $status === 'pass' => 'pass',
            $status === 'skip' => 'skip',
            default => 'unknown',
        };

        $recommendation = match ($severity) {
            'block' => __('Resolve this issue before initiating live M-Pesa traffic.'),
            'warn' => __('Review this warning before go-live; enable strict mode to treat as blocking.'),
            'skip' => __('Optional check was skipped.'),
            'pass' => __('No action required.'),
            default => __('Review this item.'),
        };

        return array_merge($item, [
            'severity' => $severity,
            'recommendation' => $recommendation,
        ]);
    }
}
