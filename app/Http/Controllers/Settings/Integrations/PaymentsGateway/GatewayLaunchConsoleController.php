<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use App\Support\PaymentsGateway\LaunchConsolePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayLaunchConsoleController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(
        Request $request,
        PaymentsGatewayClient $client,
        LaunchConsolePresenter $presenter,
    ): View {
        $console = $presenter->build($client, [
            'paybill_account_uuid' => $request->query('paybill_account_uuid'),
            'environment' => $request->query('environment', 'production'),
            'validation_run_uuid' => $request->query('validation_run_uuid'),
        ]);

        return view('settings.integrations.payments-gateway.launch-console.index', [
            ...$console,
            'severityVariant' => fn (string $severity) => $this->launchSeverityVariant($severity),
            'severityLabel' => fn (string $severity) => $this->launchSeverityLabel($severity),
            'formatTimestamp' => fn (?string $value) => $this->formatGatewayTimestamp($value),
            'shortUuid' => fn (?string $uuid) => $this->shortUuid($uuid),
        ]);
    }

    public function panel(
        Request $request,
        string $panel,
        PaymentsGatewayClient $client,
        LaunchConsolePresenter $presenter,
    ): View {
        $paybillUuid = filled($request->query('paybill_account_uuid'))
            ? (string) $request->query('paybill_account_uuid')
            : null;
        $environment = (string) $request->query('environment', 'production');
        $missingApis = [];

        $payload = match ($panel) {
            'operational-status' => [
                'operationalStatus' => $presenter->buildOperationalStatus($client, $missingApis),
                'missingApis' => array_values(array_unique($missingApis)),
            ],
            'paybill-readiness' => [
                'paybillReadiness' => $paybillUuid !== null
                    ? $presenter->buildPaybillReadiness($client, $paybillUuid, $environment, $missingApis)
                    : null,
                'filters' => [
                    'paybill_account_uuid' => $paybillUuid ?? '',
                    'environment' => $environment,
                ],
                'environments' => GatewayFormOptions::paymentEnvironments(),
                'isLiveEnvironment' => strtolower($environment) === 'production',
                'missingApis' => array_values(array_unique($missingApis)),
            ],
            'validation-history' => [
                'validationHistory' => $presenter->buildValidationHistory($client, array_filter([
                    'paybill_account_uuid' => $paybillUuid,
                    'per_page' => 8,
                ]), $missingApis),
                'missingApis' => array_values(array_unique($missingApis)),
            ],
            'incidents' => [
                'escalation' => $presenter->buildEscalationWorkspace($client, $missingApis),
                'missingApis' => array_values(array_unique($missingApis)),
            ],
            default => abort(404),
        };

        return view('settings.integrations.payments-gateway.launch-console.partials.panel-'.$panel, [
            ...$payload,
            'severityVariant' => fn (string $severity) => $this->launchSeverityVariant($severity),
            'severityLabel' => fn (string $severity) => $this->launchSeverityLabel($severity),
            'formatTimestamp' => fn (?string $value) => $this->formatGatewayTimestamp($value),
            'shortUuid' => fn (?string $uuid) => $this->shortUuid($uuid),
        ]);
    }

    public function runValidation(Request $request, PaymentsGatewayClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'environment' => ['required', 'in:'.implode(',', GatewayFormOptions::paymentEnvironments())],
            'paybill_account_uuid' => ['required', 'uuid'],
            'stk_transaction_uuid' => ['nullable', 'uuid'],
            'c2b_transaction_uuid' => ['nullable', 'uuid'],
            'b2c_payout_uuid' => ['nullable', 'uuid'],
            'strict_mode' => ['nullable', 'boolean'],
        ]);

        $payload = array_filter([
            'environment' => $validated['environment'],
            'paybill_account_uuid' => $validated['paybill_account_uuid'],
            'stk_transaction_uuid' => $validated['stk_transaction_uuid'] ?? null,
            'c2b_transaction_uuid' => $validated['c2b_transaction_uuid'] ?? null,
            'b2c_payout_uuid' => $validated['b2c_payout_uuid'] ?? null,
            'strict_mode' => $request->boolean('strict_mode'),
        ], fn (mixed $value) => $value !== null && $value !== '');

        $response = $client->createValidationRun($payload);

        if (($response['status'] ?? 0) === 404) {
            return redirect()
                ->route('settings.payments-gateway.launch-console', [
                    'paybill_account_uuid' => $validated['paybill_account_uuid'],
                    'environment' => $validated['environment'],
                ])
                ->with('gateway_error', __('Operational validation API not available yet on payments.pradytecai.com.'));
        }

        if ($response['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.launch-console')
                ->with('gateway_error', $this->gatewayUnavailableMessage($response));
        }

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.launch-console', [
                'paybill_account_uuid' => $validated['paybill_account_uuid'],
                'environment' => $validated['environment'],
            ]);
        }

        $run = $client->extractResource($response);
        $runUuid = is_array($run) ? (string) ($run['uuid'] ?? '') : '';

        return redirect()
            ->route('settings.payments-gateway.launch-console', array_filter([
                'paybill_account_uuid' => $validated['paybill_account_uuid'],
                'environment' => $validated['environment'],
                'validation_run_uuid' => filled($runUuid) ? $runUuid : null,
            ]))
            ->with('status', __('Observational validation run completed on Payments Gateway. No Daraja transactions were initiated.'));
    }

    protected function launchSeverityVariant(string $severity): string
    {
        return match ($severity) {
            LaunchConsolePresenter::SEVERITY_PASS => 'success',
            LaunchConsolePresenter::SEVERITY_WARNING => 'warning',
            LaunchConsolePresenter::SEVERITY_BLOCKED => 'danger',
            default => 'neutral',
        };
    }

    protected function launchSeverityLabel(string $severity): string
    {
        return match ($severity) {
            LaunchConsolePresenter::SEVERITY_PASS => __('PASS'),
            LaunchConsolePresenter::SEVERITY_WARNING => __('WARNING'),
            LaunchConsolePresenter::SEVERITY_BLOCKED => __('BLOCKED'),
            default => strtoupper($severity),
        };
    }
}
