<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\IncidentInvestigationPresenter;
use Illuminate\View\View;

class GatewayIncidentInvestigationController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function showDeadLetter(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.dead-letters.show', [
            ...$presenter->presentDeadLetter($client, $uuid),
            'statusVariant' => fn (string $status): string => $this->monitoringStatusVariant($status),
        ]);
    }

    public function showCallbackLog(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.callback-logs.show', [
            ...$presenter->presentCallbackLog($client, $uuid),
            'statusVariant' => fn (string $status): string => match (strtolower($status)) {
                'processed', 'matched', 'success' => 'success',
                'received', 'pending' => 'warning',
                'failed', 'duplicate', 'ignored' => 'danger',
                default => 'neutral',
            },
        ]);
    }

    public function showWebhookDelivery(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.webhook-deliveries.show', [
            ...$presenter->presentWebhookDelivery($client, $uuid),
            'statusVariant' => fn (string $status): string => match (strtolower($status)) {
                'success' => 'success',
                'pending' => 'warning',
                'failed' => 'danger',
                default => 'neutral',
            },
        ]);
    }

    public function showTreasuryAlert(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.treasury-alerts.show', [
            ...$presenter->presentTreasuryAlert($client, $uuid),
            'statusVariant' => fn (string $status): string => match (strtolower($status)) {
                'resolved', 'closed' => 'success',
                'acknowledged' => 'warning',
                'open', 'new' => 'danger',
                default => 'neutral',
            },
        ]);
    }

    public function showWebhookEvent(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.webhook-events.show', [
            ...$presenter->presentWebhookEvent($client, $uuid),
            'statusVariant' => fn (string $status): string => match (strtolower($status)) {
                'delivered', 'success' => 'success',
                'pending' => 'warning',
                'failed' => 'danger',
                default => 'neutral',
            },
        ]);
    }

    public function showUnmatchedTransaction(
        string $uuid,
        PaymentsGatewayClient $client,
        IncidentInvestigationPresenter $presenter,
    ): View {
        return view('settings.integrations.payments-gateway.operations-console.unmatched-transactions.show', [
            ...$presenter->presentUnmatchedTransaction($client, $uuid),
            'statusVariant' => fn (string $status): string => match (strtolower($status)) {
                'resolved', 'closed' => 'success',
                'open' => 'danger',
                default => 'warning',
            },
        ]);
    }
}
