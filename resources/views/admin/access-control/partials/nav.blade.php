@php
    $item = fn (bool $active) => $active
        ? 'bg-gradient-to-r from-indigo-500/15 to-violet-500/10 text-indigo-700 ring-1 ring-indigo-500/20 dark:text-indigo-200'
        : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/60';
@endphp

<nav class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ __('Access Control') }}</p>
    </div>
    <ul class="p-2 text-sm font-medium">
        <li><a href="{{ route('access-control.permissions.index') }}" class="{{ $item(request()->routeIs('access-control.permissions.*')) }} block rounded-xl px-3 py-2">{{ __('Permissions registry') }}</a></li>
        <li><a href="{{ route('access-control.roles.index') }}" class="{{ $item(request()->routeIs('access-control.roles.*')) }} block rounded-xl px-3 py-2">{{ __('Roles') }}</a></li>
        <li><a href="{{ route('access-control.assignments.index') }}" class="{{ $item(request()->routeIs('access-control.assignments.*')) }} block rounded-xl px-3 py-2">{{ __('User role assignments') }}</a></li>
        <li><a href="{{ route('access-control.switch-logs.index') }}" class="{{ $item(request()->routeIs('access-control.switch-logs.*')) }} block rounded-xl px-3 py-2">{{ __('Role switch logs') }}</a></li>
    </ul>
</nav>
