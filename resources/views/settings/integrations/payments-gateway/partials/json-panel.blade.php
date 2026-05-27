@props([
    'title',
    'payload',
    'collapsible' => false,
    'copyable' => false,
    'redacted' => false,
    'truncateAt' => 8000,
])

@php
    $formatted = is_string($payload)
        ? $payload
        : json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $formatted = filled($formatted) ? $formatted : '—';
    $truncated = strlen($formatted) > $truncateAt;
    $displayPayload = $truncated ? substr($formatted, 0, $truncateAt)."\n\n… [truncated]" : $formatted;
    $panelId = 'json-panel-'.md5($title.(string) $truncateAt);
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    @if ($collapsible)
        <details class="group" open>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    @if ($redacted)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800 dark:bg-amber-950 dark:text-amber-200">{{ __('Redacted') }}</span>
                    @endif
                    @if ($truncated)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Truncated') }}</span>
                    @endif
                </div>
                <span class="text-xs text-slate-500 group-open:hidden">{{ __('Expand') }}</span>
            </summary>
            <div class="mt-3">
                @if ($copyable && $displayPayload !== '—')
                    <div class="mb-2 flex justify-end">
                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" onclick="window.copyJsonPanel(@js($panelId))">{{ __('Copy') }}</button>
                    </div>
                @endif
                <pre id="{{ $panelId }}" class="max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ $displayPayload }}</pre>
            </div>
        </details>
    @else
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
        <pre class="mt-3 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ $displayPayload }}</pre>
    @endif
</div>

@once
    <script>
        window.copyJsonPanel = function (panelId) {
            const element = document.getElementById(panelId);
            if (! element) {
                return;
            }

            navigator.clipboard?.writeText(element.textContent ?? '').catch(function () {});
        };
    </script>
@endonce
