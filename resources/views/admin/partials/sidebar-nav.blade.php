@php
    $link = function (bool $active): string {
        return $active
            ? 'bg-gradient-to-r from-indigo-500/20 to-violet-500/10 text-white shadow-inner shadow-indigo-500/10 ring-1 ring-inset ring-white/10'
            : 'text-slate-400 hover:bg-white/5 hover:text-white';
    };
@endphp

<nav class="flex flex-1 flex-col gap-0.5 px-2 text-[13px] font-medium">
    <p class="mb-1 mt-1 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Overview') }}</p>
    <p class="mb-1 mt-1 hidden px-2 text-center text-[9px] font-bold uppercase tracking-widest text-slate-600 lg:block" x-show="sidebarCollapsed" x-cloak>·</p>

    @permission('dashboard.view')
    <a href="{{ route('dashboard') }}" class="{{ $link(request()->routeIs('dashboard')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Overview') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-6 10.5 6M4.5 19.5h15" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Overview') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Infrastructure') }}</p>
    @permission('servers.view')
    <a href="{{ route('servers.index') }}" class="{{ $link(request()->routeIs('servers.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Servers') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Servers') }}</span>
    </a>
    @endpermission
    @permission('projects.view')
    <a href="{{ route('projects.index') }}" class="{{ $link(request()->routeIs('projects.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Hosted Projects') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15a2.25 2.25 0 012.25 2.25v.75m-18 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-18 0v-1.5A2.25 2.25 0 014.5 9h15a2.25 2.25 0 012.25 2.25v1.5" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Hosted Projects') }}</span>
    </a>
    @endpermission
    @permission('ssl.view')
    <a href="{{ route('ssl-domains.index') }}" class="{{ $link(request()->routeIs('ssl-domains.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('SSL & Domains') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286zm0 13.036h.008v.008H12v-.008z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('SSL & Domains') }}</span>
    </a>
    @endpermission
    @permission('backups.view')
    <a href="{{ route('backups.index') }}" class="{{ $link(request()->routeIs('backups.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Backups') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Backups') }}</span>
    </a>
    @endpermission
    @permission('server_health.view')
    <a href="{{ route('server-health.index') }}" class="{{ $link(request()->routeIs('server-health.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Server Health') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-3.75v1.5" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Server Health') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Tenant management') }}</p>
    @permission('tenants.view')
    <a href="{{ route('tenants.index') }}" class="{{ $link(request()->routeIs('tenants.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Tenants') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Tenants') }}</span>
    </a>
    @endpermission
    @permission('subscriptions.view')
    <a href="{{ route('subscriptions.index') }}" class="{{ $link(request()->routeIs('subscriptions.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Subscriptions') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3 3.75h10.5a2.25 2.25 0 002.25-2.25V15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 15v3.75A2.25 2.25 0 006.75 21z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Subscriptions') }}</span>
    </a>
    @endpermission
    @permission('license_logs.view')
    <a href="{{ route('license-logs.index') }}" class="{{ $link(request()->routeIs('license-logs.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('License Logs') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('License Logs') }}</span>
    </a>
    @endpermission
    @permission('tenant_access_controls.view')
    <a href="{{ route('access-controls.index') }}" class="{{ $link(request()->routeIs('access-controls.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Access Controls') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Access Controls') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Financials') }}</p>
    @permission('invoices.view')
    <a href="{{ route('invoices.index') }}" class="{{ $link(request()->routeIs('invoices.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Invoices') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Invoices') }}</span>
    </a>
    @endpermission
    @permission('payments.view')
    <a href="{{ route('payments.index') }}" class="{{ $link(request()->routeIs('payments.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Payments') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .621-.504 1.125-1.125 1.125H3.75m19.5-1.5H21M3.75 20.25h17.25m-17.25-3h17.25m-17.25-3h17.25" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Payments') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Operations') }}</p>
    @permission('deployments.view')
    <a href="{{ route('deployments.index') }}" class="{{ $link(request()->routeIs('deployments.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Deployments') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a6 6 0 001.666 5.022" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Deployments') }}</span>
    </a>
    @endpermission
    @permission('monitoring.view')
    <a href="{{ route('monitoring.index') }}" class="{{ $link(request()->routeIs('monitoring.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Monitoring') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Monitoring') }}</span>
    </a>
    @endpermission
    @permission('risk_center.view')
    <a href="{{ route('risk-center.index') }}" class="{{ $link(request()->routeIs('risk-center.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Risk Center') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Risk Center') }}</span>
    </a>
    @endpermission
    @permission('activity_logs.view')
    <a href="{{ route('activity-logs.index') }}" class="{{ $link(request()->routeIs('activity-logs.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Activity Logs') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Activity Logs') }}</span>
    </a>
    @endpermission
    @permission('support.tickets.view')
    <a href="{{ route('support-tickets.index') }}" class="{{ $link(request()->routeIs('support-tickets.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Support Tickets') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a23.922 23.922 0 0112.204 3.66c1.115.885 1.51 2.316.94 3.572a3.105 3.105 0 01-.037.043 12.404 12.404 0 01-4.255 1.5c-.188.028-.377.052-.566.075-1.456.194-2.911.292-4.371.292-1.39 0-2.781-.085-4.163-.254a12.404 12.404 0 01-4.255-1.5 3.105 3.105 0 01-.037-.043.75.75 0 01.631-1.151 23.922 23.922 0 0012.204-3.66.75.75 0 01.631.151c.41.314.68.81.68 1.362 0 .548-.27 1.048-.68 1.362z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Support Tickets') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('People') }}</p>
    @permission('hr.staff.view')
    <a href="{{ route('hr.index') }}" class="{{ $link(request()->routeIs('hr.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('HR & Team') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772M15 12.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('HR & Team') }}</span>
    </a>
    @endpermission

    <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed" x-transition>{{ __('Settings') }}</p>
    @permission('rbac.manage')
    <a href="{{ route('access-control.permissions.index') }}" class="{{ $link(request()->routeIs('access-control.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Access Control') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Access Control') }}</span>
    </a>
    @endpermission
    <a href="{{ route('users-roles.index') }}" class="{{ $link(request()->routeIs('users-roles.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('Users & Roles') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Users & Roles') }}</span>
    </a>
    @permission('system_settings.update')
    <a href="{{ route('system-settings.edit') }}" class="{{ $link(request()->routeIs('system-settings.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('System Settings') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('System Settings') }}</span>
    </a>
    @endpermission
    @permission('api_credentials.view')
    <a href="{{ route('api-credentials.index') }}" class="{{ $link(request()->routeIs('api-credentials.*')) }} group flex items-center gap-3 rounded-xl px-3 py-2 transition" title="{{ __('API & Integrations') }}">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
        </span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('API & Integrations') }}</span>
    </a>
    @endpermission
    @permission('payments_gateway.view')
    <a href="{{ route('settings.payments-gateway.overview') }}" class="{{ $link(request()->routeIs('settings.payments-gateway.*') && ! request()->routeIs(['settings.payments-gateway.operations-console', 'settings.payments-gateway.transactions.*', 'settings.payments-gateway.callback-logs.*', 'settings.payments-gateway.webhook-events.*', 'settings.payments-gateway.webhook-deliveries.*'])) }} group ml-3 flex items-center gap-3 rounded-xl px-3 py-2 transition lg:ml-4" title="{{ __('Payments Gateway') }}">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
        </span>
        <span class="truncate text-sm" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Payments Gateway') }}</span>
    </a>
    @php
        $paymentsGatewayMonitoringLinks = [
            ['route' => 'settings.payments-gateway.operations-console', 'label' => __('Operations Console'), 'pattern' => 'settings.payments-gateway.operations-console'],
            ['route' => 'settings.payments-gateway.transactions.index', 'label' => __('Transactions'), 'pattern' => 'settings.payments-gateway.transactions.*'],
            ['route' => 'settings.payments-gateway.callback-logs.index', 'label' => __('Callback Logs'), 'pattern' => 'settings.payments-gateway.callback-logs.*'],
            ['route' => 'settings.payments-gateway.webhook-events.index', 'label' => __('Webhook Events'), 'pattern' => 'settings.payments-gateway.webhook-events.*'],
            ['route' => 'settings.payments-gateway.webhook-deliveries.index', 'label' => __('Webhook Deliveries'), 'pattern' => 'settings.payments-gateway.webhook-deliveries.*'],
            ['route' => 'settings.payments-gateway.production-readiness', 'label' => __('Production Readiness'), 'pattern' => 'settings.payments-gateway.production-readiness'],
            ['route' => 'settings.payments-gateway.go-live-dry-run', 'label' => __('Go-Live Dry Run'), 'pattern' => 'settings.payments-gateway.go-live-dry-run'],
        ];
    @endphp
    @foreach ($paymentsGatewayMonitoringLinks as $monitoringLink)
        <a href="{{ route($monitoringLink['route']) }}" class="{{ $link(request()->routeIs($monitoringLink['pattern'])) }} group ml-6 flex items-center gap-2 rounded-xl px-3 py-1.5 transition lg:ml-8" title="{{ $monitoringLink['label'] }}">
            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-500"></span>
            <span class="truncate text-xs" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $monitoringLink['label'] }}</span>
        </a>
    @endforeach
    @endpermission
</nav>
