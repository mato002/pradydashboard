@if ($pagination && ($pagination['last_page'] ?? 1) > 1)
    <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 text-sm dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-slate-500 dark:text-slate-400">
            {{ __('Showing :from–:to of :total', [
                'from' => $pagination['from'] ?? 0,
                'to' => $pagination['to'] ?? 0,
                'total' => $pagination['total'] ?? 0,
            ]) }}
            · {{ __('Page :page of :last', ['page' => $pagination['current_page'], 'last' => $pagination['last_page']]) }}
        </p>
        <div class="flex gap-2">
            @if (($pagination['current_page'] ?? 1) > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    {{ __('Previous') }}
                </a>
            @endif
            @if (($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1))
                <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    {{ __('Next') }}
                </a>
            @endif
        </div>
    </div>
@endif
