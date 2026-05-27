@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'pass' => 'success',
        'warn', 'warning' => 'warning',
        'fail' => 'danger',
        default => 'neutral',
    };

    $statusLabel = fn (string $status): string => match (strtolower($status)) {
        'pass' => __('Pass'),
        'warn', 'warning' => __('Warning'),
        'fail' => __('Fail'),
        'skip' => __('Skipped'),
        default => ucfirst($status ?: __('Unknown')),
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Production Readiness')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($contextualLaunch ?? false)
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                {{ __('Production readiness opens in context of this PayBill.') }}
            </div>
        @endif

        <form method="get" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <input type="hidden" name="run" value="1">
            <div class="grid gap-3 md:grid-cols-3">
                @include('settings.integrations.payments-gateway.partials.form-field', [
                    'label' => __('PayBill Account UUID'),
                    'name' => 'paybill_account_uuid',
                    'value' => $filters['paybill_account_uuid'],
                    'placeholder' => '00000000-0000-0000-0000-000000000000',
                ])
                <div class="flex items-end">
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200/80 px-3 py-2 text-sm dark:border-slate-800">
                        <input type="checkbox" name="test_oauth" value="1" @checked($filters['test_oauth']) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900">
                        <span>{{ __('Test OAuth') }}</span>
                    </label>
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.production-readiness') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Run Check') }}</button>
            </div>
        </form>

        @if (! $gatewayUnavailable && $report)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Overall readiness') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('Report generated at :time', ['time' => filled($summary['generated_at']) ? \Illuminate\Support\Carbon::parse($summary['generated_at'])->format('M j, Y H:i:s') : '—']) }}
                            · {{ __('Gateway response :ms ms', ['ms' => $responseTimeMs]) }}
                        </p>
                    </div>
                    <x-ui.status-badge :variant="$statusVariant($summary['overall_status'])">{{ $statusLabel($summary['overall_status']) }}</x-ui.status-badge>
                </div>

                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                    @foreach ([
                        'database' => __('Database'),
                        'queue' => __('Queue'),
                        'daraja' => __('Daraja'),
                        'callbacks' => __('Callbacks'),
                        'workers' => __('Workers'),
                        'security' => __('Security'),
                        'treasury' => __('Treasury'),
                    ] as $key => $label)
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/40">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                            <dd class="mt-2">
                                <x-ui.status-badge :variant="$statusVariant($summary['sections'][$key]['overall_status'] ?? 'unknown')">
                                    {{ $statusLabel($summary['sections'][$key]['overall_status'] ?? 'unknown') }}
                                </x-ui.status-badge>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-rose-200/80 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/40">
                    <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-100">{{ __('Issues') }} ({{ count($summary['issues']) }})</h3>
                    @forelse ($summary['issues'] as $issue)
                        <div class="mt-3 rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm dark:border-rose-900 dark:bg-slate-900/60">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $issue['label'] ?? $issue['key'] ?? __('Issue') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $issue['section'] ?? '' }} · {{ $issue['message'] ?? '—' }}</p>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-rose-800 dark:text-rose-200">{{ __('No failing checks.') }}</p>
                    @endforelse
                </div>

                <div class="rounded-2xl border border-amber-200/80 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/40">
                    <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('Warnings') }} ({{ count($summary['warnings']) }})</h3>
                    @forelse ($summary['warnings'] as $warning)
                        <div class="mt-3 rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm dark:border-amber-900 dark:bg-slate-900/60">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $warning['label'] ?? $warning['key'] ?? __('Warning') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $warning['section'] ?? '' }} · {{ $warning['message'] ?? '—' }}</p>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-amber-800 dark:text-amber-200">{{ __('No warnings.') }}</p>
                    @endforelse
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Recommendations') }} ({{ count($summary['recommendations']) }})</h3>
                    @forelse ($summary['recommendations'] as $recommendation)
                        <div class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/60">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $recommendation['label'] ?? __('Recommendation') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $recommendation['section'] ?? '' }} · {{ $recommendation['message'] ?? '—' }}</p>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">{{ __('No recommendations.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Environment'),
                    'overallStatus' => $summary['sections']['environment']['overall_status'],
                    'checks' => $summary['sections']['environment']['checks'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Database'),
                    'overallStatus' => $summary['sections']['database']['overall_status'],
                    'checks' => $summary['sections']['database']['checks'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Queue'),
                    'overallStatus' => $summary['sections']['queue']['overall_status'],
                    'checks' => $summary['sections']['queue']['checks'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Daraja'),
                    'overallStatus' => $summary['sections']['daraja']['overall_status'],
                    'checks' => $summary['sections']['daraja']['checks'],
                    'message' => $summary['sections']['daraja']['message'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Callbacks'),
                    'overallStatus' => $summary['sections']['callbacks']['overall_status'],
                    'checks' => $summary['sections']['callbacks']['checks'],
                    'expectedUrls' => $summary['sections']['callbacks']['expected_urls'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Workers'),
                    'overallStatus' => $summary['sections']['workers']['overall_status'],
                    'checks' => $summary['sections']['workers']['checks'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Security'),
                    'overallStatus' => $summary['sections']['security']['overall_status'],
                    'checks' => $summary['sections']['security']['checks'],
                ])
                @include('settings.integrations.payments-gateway.partials.readiness-section', [
                    'title' => __('Treasury'),
                    'overallStatus' => $summary['sections']['treasury']['overall_status'],
                    'checks' => $summary['sections']['treasury']['checks'],
                ])
            </div>
        @elseif (! $gatewayUnavailable)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Production readiness report could not be parsed from Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
