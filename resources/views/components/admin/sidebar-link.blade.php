@props([
    'href',
    'active' => false,
    'label',
    'icon' => null,
    'nested' => false,
])

@php
    $linkClass = $active
        ? 'bg-gradient-to-r from-indigo-500/20 to-violet-500/10 text-white shadow-inner shadow-indigo-500/10 ring-1 ring-inset ring-white/10'
        : 'text-slate-400 hover:bg-white/5 hover:text-white';
@endphp

<a
    href="{{ $href }}"
    data-prady-nav
    @click="if ($root.sidebarCollapsed) { $dispatch('sidebar-close-flyout') }"
    {{ $attributes->merge(['class' => trim("group flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium transition {$linkClass} " . ($nested ? 'pl-9' : ''))]) }}
    title="{{ $label }}"
>
    @if ($icon)
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            {!! $icon !!}
        </span>
    @elseif ($nested)
        <span @class(['ml-1 h-1.5 w-1.5 shrink-0 rounded-full', 'bg-indigo-400' => $active, 'bg-slate-500' => ! $active])></span>
    @endif
    <span class="sidebar-link-label truncate" :class="$root.sidebarCollapsed ? 'lg:hidden' : ''">{{ $label }}</span>
</a>
