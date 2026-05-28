<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Jobs\Webhooks\DeliverWebhookEventJob;
use App\Services\PaymentsGateway\OperationsBulkActionService;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use App\Support\PaymentsGateway\OperationsConsolePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GatewayOperationsConsoleController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(
        Request $request,
        PaymentsGatewayClient $client,
        OperationsConsolePresenter $presenter,
    ): View {
        $console = $presenter->build($client, [
            'transaction_type' => $request->query('transaction_type'),
            'transaction_status' => $request->query('transaction_status'),
        ]);

        return view('settings.integrations.payments-gateway.operations-console.index', [
            ...$console,
            'transactionTypes' => GatewayFormOptions::transactionTypes(),
            'transactionStatuses' => GatewayFormOptions::transactionStatuses(),
            'formatTimestamp' => fn (?string $value) => $this->formatGatewayTimestamp($value),
            'shortUuid' => fn (?string $uuid) => $this->shortUuid($uuid),
            'statusVariant' => fn (string $status) => $this->monitoringStatusVariant($status),
        ]);
    }

    public function redispatchWebhookDelivery(string $deliveryUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        DeliverWebhookEventJob::dispatch('', false, $deliveryUuid);

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Webhook delivery redispatch queued for the Payments Gateway.'));
    }

    public function replayDeadLetter(string $deadLetterUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->replayDeadLetter($deadLetterUuid);

        if (! ($response['ok'] ?? false)) {
            if (($response['status'] ?? 0) === 404) {
                return redirect()
                    ->route('settings.payments-gateway.operations-console')
                    ->with('gateway_error', __('Operation API not available yet.'));
            }

            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.operations-console');
        }

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Dead letter replay submitted on Payments Gateway.'));
    }

    public function discardDeadLetter(string $deadLetterUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->discardDeadLetter($deadLetterUuid);

        if (! ($response['ok'] ?? false)) {
            if (($response['status'] ?? 0) === 404) {
                return redirect()
                    ->route('settings.payments-gateway.operations-console')
                    ->with('gateway_error', __('Operation API not available yet.'));
            }

            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.operations-console');
        }

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Dead letter discarded on Payments Gateway.'));
    }

    public function retryCallback(string $callbackLogUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->retryCallback($callbackLogUuid);

        if (! ($response['ok'] ?? false)) {
            if (($response['status'] ?? 0) === 404) {
                return redirect()
                    ->route('settings.payments-gateway.operations-console')
                    ->with('gateway_error', __('Operation API not available yet.'));
            }

            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.operations-console');
        }

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Callback retry submitted on Payments Gateway.'));
    }

    public function acknowledgeAlert(Request $request, string $alertUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->acknowledgeTreasuryAlert($alertUuid, array_filter([
            'comments' => $request->input('comments'),
        ]));

        if (! ($response['ok'] ?? false)) {
            if (($response['status'] ?? 0) === 404) {
                return redirect()
                    ->route('settings.payments-gateway.operations-console')
                    ->with('gateway_error', __('Operation API not available yet.'));
            }

            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.operations-console');
        }

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Treasury alert acknowledged on Payments Gateway.'));
    }

    public function resolveAlert(Request $request, string $alertUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->resolveTreasuryAlert($alertUuid, array_filter([
            'comments' => $request->input('comments'),
        ]));

        if (! ($response['ok'] ?? false)) {
            if (($response['status'] ?? 0) === 404) {
                return redirect()
                    ->route('settings.payments-gateway.operations-console')
                    ->with('gateway_error', __('Operation API not available yet.'));
            }

            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.operations-console');
        }

        return redirect()
            ->route('settings.payments-gateway.operations-console')
            ->with('status', __('Treasury alert resolved on Payments Gateway.'));
    }

    public function bulkAction(
        Request $request,
        OperationsBulkActionService $bulkActionService,
        PaymentsGatewayClient $client,
    ): RedirectResponse {
        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(OperationsBulkActionService::supportedActions())],
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'uuid'],
            'comments' => ['nullable', 'string', 'max:500'],
        ]);

        $summary = $bulkActionService->execute(
            $client,
            $validated['action'],
            $validated['uuids'],
            $validated['comments'] ?? null,
        );

        $redirect = redirect()->route('settings.payments-gateway.operations-console');

        $statusMessage = __('Bulk action completed: :succeeded succeeded, :failed failed.', [
            'succeeded' => $summary['succeeded'],
            'failed' => $summary['failed'],
        ]);

        if ($summary['failed'] > 0) {
            return $redirect
                ->with('status', $statusMessage)
                ->with('bulk_action_errors', $summary['errors']);
        }

        return $redirect->with('status', $statusMessage);
    }
}
