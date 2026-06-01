<div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $title }}</p>
    <ul class="mt-2 space-y-2">
        @forelse ($items as $item)
            <li class="flex flex-wrap items-start justify-between gap-2 rounded-lg border border-slate-200/80 px-3 py-2 text-xs dark:border-slate-800">
                <div>
                    <p class="font-medium">{{ $item['type'] ?? $item['category'] ?? $item['failure_reason'] ?? $shortUuid($item[$linkKey] ?? null) }}</p>
                    <p class="text-slate-500">{{ $formatTimestamp($item['created_at'] ?? $item['failed_at'] ?? null) }}</p>
                </div>
                <div class="flex flex-col gap-1 text-right font-semibold">
                    @if (filled($item[$linkKey] ?? null))
                        <a href="{{ route($linkRoute, $item[$linkKey]) }}" class="text-indigo-600 dark:text-indigo-400">{{ __('Investigate') }}</a>
                    @endif
                    @permission('payments_gateway.manage')
                        @if (isset($actionRoute, $actionKey, $actionLabel) && filled($item[$actionKey] ?? null))
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route($actionRoute, $item[$actionKey]),
                                'label' => $actionLabel,
                                'confirm' => __('Submit this remediation action to payments.pradytecai.com?'),
                            ])
                        @endif
                    @endpermission
                </div>
            </li>
        @empty
            <li class="text-slate-500">{{ $empty }}</li>
        @endforelse
    </ul>
</div>
