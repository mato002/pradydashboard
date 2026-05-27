@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'pass' => 'success',
        'warn', 'warning' => 'warning',
        'fail', 'block', 'blocked' => 'danger',
        default => 'neutral',
    };

    $statusLabel = fn (string $status): string => match (strtolower($status)) {
        'pass' => __('Pass'),
        'warn', 'warning' => __('Warning'),
        'fail' => __('Fail'),
        'block', 'blocked' => __('Blocked'),
        'skip' => __('Skipped'),
        default => ucfirst($status ?: __('Unknown')),
    };

    $groupLabels = [
        'environment' => __('Environment'),
        'daraja' => __('Daraja'),
        'callbacks' => __('Callbacks'),
        'queue' => __('Queue'),
        'workers' => __('Workers'),
        'security' => __('Security'),
        'treasury' => __('Treasury'),
        'webhooks' => __('Webhooks'),
    ];
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Go-Live Dry Run')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($contextualLaunch ?? false)
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                {{ __('Go-live dry run opens in context of this PayBill.') }}
            </div>
        @endif

        <form method="get" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <input type="hidden" name="run" value="1">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @include('settings.integrations.payments-gateway.partials.form-field', [
                    'label' => __('PayBill Account UUID'),
                    'name' => 'paybill_account_uuid',
                    'value' => $filters['paybill_account_uuid'],
                    'placeholder' => '00000000-0000-0000-0000-000000000000',
                    'required' => true,
                ])
                <div class="flex flex-col justify-end gap-2 md:col-span-2 xl:col-span-3 md:flex-row md:items-end">
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200/80 px-3 py-2 text-sm dark:border-slate-800">
                        <input type="checkbox" name="skip_oauth" value="1" @checked($filters['skip_oauth']) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                        <span>{{ __('Skip OAuth') }}</span>
                    </label>
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200/80 px-3 py-2 text-sm dark:border-slate-800">
                        <input type="checkbox" name="strict" value="1" @checked($filters['strict']) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                        <span>{{ __('Strict mode') }}</span>
                    </label>
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.go-live-dry-run') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Run Dry Run') }}</button>
            </div>
        </form>

        @if ($ranDryRun && ! $gatewayUnavailable && $report)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Dry run result') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('Report generated at :time', ['time' => filled($report['generated_at'] ?? null) ? \Illuminate\Support\Carbon::parse($report['generated_at'])->format('M j, Y H:i:s') : '—']) }}
                            · {{ __('Gateway response :ms ms', ['ms' => $responseTimeMs]) }}
                        </p>
                    </div>
                    <x-ui.status-badge :variant="$statusVariant((string) ($report['overall_status'] ?? 'unknown'))">
                        {{ $statusLabel((string) ($report['overall_status'] ?? 'unknown')) }}
                    </x-ui.status-badge>
                </div>

                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Account UUID'), 'value' => $report['paybill_account_uuid'] ?? '—'])
                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Account name'), 'value' => $report['account_name'] ?? '—'])
                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Environment'), 'value' => $report['environment'] ?? '—'])
                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Strict mode'), 'value' => ($report['strict_mode'] ?? false) ? __('Yes') : __('No')])
                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Readiness baseline'), 'value' => $report['readiness_overall'] ?? '—'])
                </dl>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-rose-200/80 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/40">
                    <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-100">{{ __('Blocking issues') }} ({{ count($report['blocking_issues'] ?? []) }})</h3>
                    <ul class="mt-3 space-y-2 text-sm text-rose-900 dark:text-rose-100">
                        @forelse ($report['blocking_issues'] ?? [] as $issue)
                            <li class="rounded-xl border border-rose-200 bg-white px-3 py-2 dark:border-rose-900 dark:bg-slate-900/60">{{ $issue }}</li>
                        @empty
                            <li class="text-rose-800 dark:text-rose-200">{{ __('No blocking issues.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-amber-200/80 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/40">
                    <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('Warnings') }} ({{ count($report['warnings'] ?? []) }})</h3>
                    <ul class="mt-3 space-y-2 text-sm text-amber-900 dark:text-amber-100">
                        @forelse ($report['warnings'] ?? [] as $warning)
                            <li class="rounded-xl border border-amber-200 bg-white px-3 py-2 dark:border-amber-900 dark:bg-slate-900/60">{{ $warning }}</li>
                        @empty
                            <li class="text-amber-800 dark:text-amber-200">{{ __('No warnings.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Next steps') }}</h3>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-700 dark:text-slate-300">
                        @forelse ($report['next_steps'] ?? [] as $step)
                            <li>{{ $step }}</li>
                        @empty
                            <li>{{ __('No next steps returned.') }}</li>
                        @endforelse
                    </ol>
                </div>
            </div>

            @foreach ($groupLabels as $groupKey => $groupLabel)
                @php $items = $groupedChecklist[$groupKey] ?? []; @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $groupLabel }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950/40">
                                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-3">{{ __('Item') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3">{{ __('Message') }}</th>
                                    <th class="px-4 py-3">{{ __('Severity') }}</th>
                                    <th class="px-4 py-3">{{ __('Recommendation') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $item['label'] ?? $item['key'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <x-ui.status-badge :variant="$statusVariant((string) ($item['status'] ?? 'unknown'))">
                                                {{ $statusLabel((string) ($item['status'] ?? 'unknown')) }}
                                            </x-ui.status-badge>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $item['message'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <x-ui.status-badge :variant="$statusVariant((string) ($item['severity'] ?? 'unknown'))">
                                                {{ $statusLabel((string) ($item['severity'] ?? 'unknown')) }}
                                            </x-ui.status-badge>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $item['recommendation'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">{{ __('No checklist items in this group.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @elseif ($ranDryRun && ! $gatewayUnavailable)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Go-live dry run report could not be parsed from Payments Gateway.') }}
            </div>
        @elseif (! $ranDryRun)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">
                {{ __('Enter a PayBill Account UUID and run the dry run to validate go-live readiness on payments.pradytecai.com.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
