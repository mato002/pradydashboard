<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayProductionReadinessController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $paybillAccountUuid = $request->query('paybill_account_uuid');
        $testOAuth = $request->boolean('test_oauth');
        $ranCheck = $request->has('run');

        $params = array_filter([
            'paybill_account_uuid' => filled($paybillAccountUuid) ? (string) $paybillAccountUuid : null,
            'test_oauth' => $request->has('test_oauth') ? ($testOAuth ? '1' : '0') : null,
        ], fn (mixed $value) => $value !== null);

        $response = $ranCheck
            ? $client->getProductionReadiness($params)
            : ['ok' => false, 'unavailable' => false, 'response_time_ms' => 0];
        $gatewayUnavailable = $ranCheck && ((bool) ($response['unavailable'] ?? false) || ! ($response['ok'] ?? false));
        $report = $gatewayUnavailable || ! $ranCheck ? null : $client->extractData($response);
        $report = is_array($report) ? $report : null;

        $summary = $this->buildReadinessSummary($report);

        return view('settings.integrations.payments-gateway.production-readiness.index', [
            'filters' => [
                'paybill_account_uuid' => (string) ($paybillAccountUuid ?? ''),
                'test_oauth' => $testOAuth,
            ],
            'ranCheck' => $ranCheck,
            'contextualLaunch' => $ranCheck && filled($paybillAccountUuid),
            'report' => $report,
            'summary' => $summary,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? __('Production readiness check could not be completed because payments.pradytecai.com is unavailable.')
                : null,
            'responseTimeMs' => $response['response_time_ms'] ?? 0,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $report
     * @return array{
     *     overall_status: string,
     *     generated_at: string|null,
     *     issues: list<array<string, mixed>>,
     *     warnings: list<array<string, mixed>>,
     *     recommendations: list<array<string, mixed>>,
     *     sections: array<string, array{overall_status: string, checks: mixed, message: string|null, expected_urls: mixed}>
     * }
     */
    protected function buildReadinessSummary(?array $report): array
    {
        if ($report === null) {
            return [
                'overall_status' => 'unknown',
                'generated_at' => null,
                'issues' => [],
                'warnings' => [],
                'recommendations' => [],
                'sections' => [],
            ];
        }

        $sections = [
            'environment' => [
                'overall_status' => (string) ($report['environment']['overall_status'] ?? 'unknown'),
                'checks' => $report['environment']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
            'database' => [
                'overall_status' => (string) ($report['database']['overall_status'] ?? 'unknown'),
                'checks' => $report['database']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
            'queue' => [
                'overall_status' => (string) ($report['queue']['overall_status'] ?? 'unknown'),
                'checks' => $report['queue']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
            'daraja' => [
                'overall_status' => (string) ($report['daraja']['overall_status'] ?? 'unknown'),
                'checks' => $report['daraja']['checks'] ?? [],
                'message' => $report['daraja']['message'] ?? null,
                'expected_urls' => null,
            ],
            'callbacks' => [
                'overall_status' => (string) ($report['callbacks']['overall_status'] ?? 'unknown'),
                'checks' => $report['callbacks']['checks'] ?? [],
                'message' => null,
                'expected_urls' => $report['callbacks']['expected_urls'] ?? null,
            ],
            'workers' => [
                'overall_status' => (string) ($report['workers']['overall_status'] ?? 'unknown'),
                'checks' => $report['workers']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
            'security' => [
                'overall_status' => (string) ($report['security']['overall_status'] ?? 'unknown'),
                'checks' => $report['security']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
            'treasury' => [
                'overall_status' => (string) ($report['treasury']['overall_status'] ?? 'unknown'),
                'checks' => $report['treasury']['checks'] ?? [],
                'message' => null,
                'expected_urls' => null,
            ],
        ];

        $flatChecks = [];

        foreach ($sections as $sectionKey => $section) {
            $flatChecks = array_merge(
                $flatChecks,
                $this->flattenReadinessChecks($section['checks'], $sectionKey)
            );
        }

        $issues = [];
        $warnings = [];
        $recommendations = [];

        foreach ($flatChecks as $check) {
            $status = strtolower((string) ($check['status'] ?? 'unknown'));

            if ($status === 'fail') {
                $issues[] = $check;
            } elseif ($status === 'warn') {
                $warnings[] = $check;
            } elseif ($status === 'skip') {
                $recommendations[] = $check;
            }
        }

        foreach ($sections as $sectionKey => $section) {
            if (filled($section['message'])) {
                $recommendations[] = [
                    'section' => $sectionKey,
                    'label' => ucfirst($sectionKey),
                    'status' => $section['overall_status'],
                    'message' => $section['message'],
                ];
            }
        }

        return [
            'overall_status' => (string) ($report['overall_status'] ?? 'unknown'),
            'generated_at' => isset($report['generated_at']) ? (string) $report['generated_at'] : null,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations,
            'sections' => $sections,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function flattenReadinessChecks(mixed $checks, string $section): array
    {
        if (! is_array($checks)) {
            return [];
        }

        if ($this->isReadinessCheckItem($checks)) {
            return [[
                'section' => $section,
                'key' => $checks['key'] ?? null,
                'label' => $checks['label'] ?? ($checks['key'] ?? __('Check')),
                'status' => $checks['status'] ?? 'unknown',
                'message' => $checks['message'] ?? null,
                'details' => $checks['details'] ?? null,
            ]];
        }

        if (isset($checks['sections']) && is_array($checks['sections'])) {
            $flat = [];

            foreach ($checks['sections'] as $subSection => $items) {
                $flat = array_merge(
                    $flat,
                    $this->flattenReadinessChecks($items, $section.'.'.$subSection)
                );
            }

            return $flat;
        }

        $flat = [];

        foreach ($checks as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if ($this->isReadinessCheckItem($value)) {
                $flat[] = [
                    'section' => $section,
                    'key' => $value['key'] ?? $key,
                    'label' => $value['label'] ?? (string) $key,
                    'status' => $value['status'] ?? 'unknown',
                    'message' => $value['message'] ?? null,
                    'details' => $value['details'] ?? null,
                ];

                continue;
            }

            if (array_is_list($value)) {
                $flat = array_merge($flat, $this->flattenReadinessChecks($value, $section.'.'.$key));
            }
        }

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isReadinessCheckItem(array $item): bool
    {
        return array_key_exists('status', $item)
            && (array_key_exists('label', $item) || array_key_exists('key', $item));
    }
}
