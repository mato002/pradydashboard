@props([
    'paginator' => null,
])

@php
    $paginator = $paginator ?? $attributes->get('paginator');
@endphp

@if ($paginator && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between']) }}>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ __('Page :current of :last · :total total', [
                'current' => $paginator->currentPage(),
                'last' => $paginator->lastPage(),
                'total' => number_format($paginator->total()),
            ]) }}
        </p>
        {{ $paginator->withQueryString()->links('vendor.pagination.prady') }}
    </div>
@elseif ($paginator)
    <p class="text-xs text-slate-500 dark:text-slate-400">
        {{ __(':total results', ['total' => number_format($paginator->total())]) }}
    </p>
@endif

