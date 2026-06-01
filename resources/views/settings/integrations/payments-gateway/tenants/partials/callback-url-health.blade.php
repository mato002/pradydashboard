@php
    $statusVariant = fn (string $status): string => match ($status) {
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_CANONICAL => 'success',
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_MISSING => 'warning',
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_LEGACY_INTERNAL,
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_MISMATCHED => 'danger',
        default => 'neutral',
    };

    $statusLabel = fn (string $status): string => match ($status) {
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_CANONICAL => __('Canonical'),
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_MISSING => __('Missing'),
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_LEGACY_INTERNAL => __('Legacy internal'),
        \App\Support\PaymentsGateway\CanonicalCallbackUrls::STATUS_MISMATCHED => __('Mismatched'),
        default => ucfirst($status),
    };
@endphp

<section id="treasury-callback-urls" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('PayBill callback URL health') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Safaricom must use the public /pay/* endpoints on payments.pradytecai.com, not internal /api/v1/callbacks/* routes.') }}</p>
        </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead>
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-2">{{ __('Field') }}</th>
                    <th class="px-3 py-2">{{ __('Canonical URL') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($canonicalCallbackReference as $row)
                    <tr>
                        <td class="px-3 py-2 font-medium">{{ $row['label'] }}</td>
                        <td class="px-3 py-2 font-mono text-xs break-all">{{ $row['url'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($paybillCallbackHealth as $healthRow)
            <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $healthRow['account_name'] }}</p>
                        <p class="text-xs text-slate-500">{{ $healthRow['shortcode'] ?? '—' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.status-badge :variant="$statusVariant($healthRow['overall_status'])">
                            {{ $statusLabel($healthRow['overall_status']) }}
                        </x-ui.status-badge>
                        @if ($healthRow['needs_url_update'])
                            <x-ui.status-badge variant="danger">{{ __('Needs URL update') }}</x-ui.status-badge>
                        @endif
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-2 py-1">{{ __('Callback') }}</th>
                                <th class="px-2 py-1">{{ __('Configured URL') }}</th>
                                <th class="px-2 py-1">{{ __('Health') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($healthRow['fields'] as $field)
                                <tr>
                                    <td class="px-2 py-1">{{ $field['label'] }}</td>
                                    <td class="px-2 py-1 font-mono break-all">{{ $field['url'] ?? '—' }}</td>
                                    <td class="px-2 py-1">
                                        <x-ui.status-badge :variant="$statusVariant($field['status'])">
                                            {{ $statusLabel($field['status']) }}
                                        </x-ui.status-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @permission('payments_gateway.manage')
                <a href="{{ route('settings.payments-gateway.paybill-accounts.edit', $healthRow['uuid']) }}" class="mt-3 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    {{ __('Update PayBill callback URLs') }}
                </a>
                @endpermission
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No PayBill accounts configured.') }}</p>
        @endforelse
    </div>
</section>
