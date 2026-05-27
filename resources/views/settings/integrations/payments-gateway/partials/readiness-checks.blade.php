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

@if (! is_array($checks) || $checks === [])
    <p class="text-sm text-slate-500">{{ __('No checks returned.') }}</p>
@elseif (isset($checks['sections']) && is_array($checks['sections']))
    <div class="space-y-4">
        @foreach ($checks['sections'] as $sectionName => $items)
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', (string) $sectionName) }}</p>
                @include('settings.integrations.payments-gateway.partials.readiness-checks', ['checks' => $items])
            </div>
        @endforeach
    </div>
@elseif (array_is_list($checks))
    <ul class="space-y-2">
        @foreach ($checks as $check)
            @if (! is_array($check))
                @continue
            @endif
            @php $status = (string) ($check['status'] ?? 'unknown'); @endphp
            <li class="rounded-xl border border-slate-200/80 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $check['label'] ?? $check['key'] ?? __('Check') }}</p>
                        @if (filled($check['message'] ?? null))
                            <p class="mt-1 text-xs text-slate-500">{{ $check['message'] }}</p>
                        @endif
                    </div>
                    <x-ui.status-badge :variant="$statusVariant($status)">{{ $statusLabel($status) }}</x-ui.status-badge>
                </div>
                @if (filled($check['details'] ?? null) && is_array($check['details']))
                    <pre class="mt-2 overflow-auto rounded-lg bg-slate-950 p-2 text-[11px] text-slate-100">{{ json_encode($check['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <ul class="space-y-2">
        @foreach ($checks as $key => $check)
            @if (! is_array($check))
                @continue
            @endif

            @if (isset($check['status']))
                @php $status = (string) ($check['status'] ?? 'unknown'); @endphp
                <li class="rounded-xl border border-slate-200/80 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $check['label'] ?? $key }}</p>
                            @if (filled($check['message'] ?? null))
                                <p class="mt-1 text-xs text-slate-500">{{ $check['message'] }}</p>
                            @endif
                        </div>
                        <x-ui.status-badge :variant="$statusVariant($status)">{{ $statusLabel($status) }}</x-ui.status-badge>
                    </div>
                </li>
            @elseif (array_is_list($check))
                <li class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', (string) $key) }}</p>
                    @include('settings.integrations.payments-gateway.partials.readiness-checks', ['checks' => $check])
                </li>
            @endif
        @endforeach
    </ul>
@endif
