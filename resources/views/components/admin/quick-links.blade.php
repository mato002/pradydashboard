@props([
    'group' => 'control_plane',
])

@php
    $links = collect(config("admin.quick_links.{$group}", []))
        ->map(function (array $link): array {
            $href = isset($link['route']) ? route($link['route']) : ($link['href'] ?? '#');
            $active = isset($link['route']) && request()->routeIs($link['route']);

            return array_merge($link, compact('href', 'active'));
        });
@endphp

@if ($links->isNotEmpty())
    <nav {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }} aria-label="{{ __('Quick links') }}">
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                @class([
                    'inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition',
                    'border-indigo-500/40 bg-indigo-500/10 text-indigo-700 dark:text-indigo-300' => $link['active'],
                    'border-slate-200/80 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' => ! $link['active'],
                ])
            >
                {{ __($link['label']) }}
            </a>
        @endforeach
    </nav>
@endif
