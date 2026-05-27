@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-slate-200/60 px-2 py-1.5 text-xs text-slate-300 dark:border-slate-700 dark:text-slate-600">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-slate-200/80 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-xs text-slate-400">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-2 py-1.5 text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-slate-200/80 bg-white px-2 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-slate-200/80 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">&rsaquo;</a>
        @else
            <span class="inline-flex min-w-[2rem] items-center justify-center rounded-lg border border-slate-200/60 px-2 py-1.5 text-xs text-slate-300 dark:border-slate-700 dark:text-slate-600">&rsaquo;</span>
        @endif
    </nav>
@endif
