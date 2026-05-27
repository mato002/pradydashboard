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

<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
            @if (filled($message ?? null))
                <p class="mt-1 text-xs text-slate-500">{{ $message }}</p>
            @endif
        </div>
        <x-ui.status-badge :variant="$statusVariant($overallStatus)">{{ $statusLabel($overallStatus) }}</x-ui.status-badge>
    </div>

    @if (filled($expectedUrls ?? null) && is_array($expectedUrls))
        <div class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Expected callback URLs') }}</p>
            <ul class="mt-2 space-y-1 font-mono text-xs text-slate-700 dark:text-slate-300">
                @foreach ($expectedUrls as $label => $url)
                    <li>
                        @if (is_string($label))
                            <span class="text-slate-500">{{ $label }}:</span>
                        @endif
                        {{ is_string($url) ? $url : json_encode($url) }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4">
        @include('settings.integrations.payments-gateway.partials.readiness-checks', ['checks' => $checks ?? []])
    </div>
</div>
