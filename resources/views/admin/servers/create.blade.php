@php
    $initialForm = [
        'name' => old('name', ''),
        'hostname' => old('meta.hostname', ''),
        'provider' => old('provider', ''),
        'ip_address' => old('ip_address', ''),
        'cpu_cores' => old('cpu_cores', ''),
        'ram_gb' => old('ram_gb', ''),
        'storage_gb' => old('storage_gb', ''),
        'disk_usage_percent' => old('disk_usage_percent', ''),
        'status' => old('status', 'unknown'),
        'ssl_status' => old('ssl_status', ''),
        'backup_status' => old('backup_status', ''),
        'monthly_cost' => old('monthly_cost', ''),
        'currency' => old('currency', 'KES'),
        'monthly_revenue' => old('monthly_revenue', ''),
        'renewal_expires_at' => old('renewal_expires_at', ''),
    ];
@endphp

<x-dashboard-layout :heading="__('Register infrastructure node')" :subheading="__('Provision production servers into the orchestration fleet')">
    <div
        class="infra-provision-shell space-y-6 pb-28 lg:pb-8"
        x-data="infrastructureProvisioning(@js($fleet))"
        x-init="Object.assign(form, @js($initialForm)); provider = form.provider || '';"
    >
        {{-- Premium header --}}
        <div class="infra-provision-hero">
            <nav class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('servers.index') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Infrastructure') }}</a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <a href="{{ route('servers.index') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Servers') }}</a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="text-indigo-600 dark:text-indigo-400">{{ __('Register node') }}</span>
            </nav>

            <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex gap-4">
                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 via-indigo-600 to-violet-600 text-white shadow-xl shadow-indigo-500/30">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 0 1 3.75 12V5.25a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25V12a2.25 2.25 0 0 1-2.25 2.25m-13.5 0h13.5" /></svg>
                        <span class="absolute -right-0.5 -top-0.5 flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-cyan-400 ring-2 ring-white dark:ring-slate-900"></span>
                        </span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white">{{ __('Register Infrastructure Node') }}</h2>
                            <span class="rounded-full bg-rose-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-700 ring-1 ring-rose-500/25 dark:text-rose-300">{{ __('Production') }}</span>
                        </div>
                        <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                            {{ __('Provision new infrastructure node into orchestration fleet — capacity, security, connectivity, and billing in one control plane.') }}
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-2 py-1 text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                {{ __('Orchestrator ready') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-500/10 px-2 py-1 dark:text-slate-300">
                                {{ __('Multi-cloud') }} · AWS · DO · Hetzner · Azure
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('servers.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('Cancel') }}
                    </a>
                    <button type="button" @click="saveDraft()" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-500/15 dark:text-indigo-300">
                        <span x-show="!draftSaved">{{ __('Save draft') }}</span>
                        <span x-show="draftSaved" x-cloak class="text-emerald-600">{{ __('Draft saved') }}</span>
                    </button>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200">
                <p class="font-semibold">{{ __('Please correct the following:') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Fleet summary KPIs --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
            <x-ui.kpi-card :title="__('Active servers')" :value="$fleet['total']" :trend="'+1'" :sublabel="__('Fleet registry')" :points="$fleet['spark']['total']" tone="indigo" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Healthy nodes')" :value="$fleet['healthy']" :trend="round(($fleet['healthy'] / max(1, $fleet['total'])) * 100).'%'" :sublabel="__('Online status')" :points="$fleet['spark']['healthy']" tone="emerald" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('SSL protected')" :value="$fleet['ssl_protected']" :sublabel="__('Valid certificates')" :points="$fleet['spark']['ssl']" tone="sky" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Backup coverage')" :value="$fleet['backup_coverage'].'%'" :animate="false" :sublabel="__('Policy compliance')" :points="$fleet['spark']['backup']" tone="violet" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('CPU capacity')" :value="$fleet['cpu_capacity']" :sublabel="__('Total cores')" :points="$fleet['spark']['cpu']" tone="amber" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg disk')" :value="$fleet['avg_disk'].'%'" :animate="false" :sublabel="__('Fleet utilization')" :points="$fleet['spark']['disk']" tone="rose" class="!p-3">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Fleet uptime')" :value="$fleet['fleet_uptime'].'%'" :animate="false" :trend="'SLA'" :sublabel="__('30-day rolling')" :points="$fleet['spark']['uptime']" tone="emerald" class="!p-3 sm:col-span-2 xl:col-span-1">
                <x-slot name="icon"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        <form method="post" action="{{ route('servers.store') }}" id="server-provision-form" class="grid gap-6 lg:grid-cols-12 lg:items-start">
            @csrf

            {{-- Section nav (tablet+) --}}
            <div class="hidden lg:col-span-12 lg:block">
                <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200/80 bg-white/60 p-2 backdrop-blur dark:border-slate-800 dark:bg-slate-900/40">
                    @foreach (['identity' => __('Identity'), 'capacity' => __('Capacity'), 'connectivity' => __('Connectivity'), 'security' => __('Security'), 'billing' => __('Billing'), 'deployment' => __('Deployment')] as $id => $label)
                        <button
                            type="button"
                            @click="scrollToSection('{{ $id }}')"
                            class="rounded-xl px-3 py-2 text-xs font-semibold transition"
                            :class="activeSection === '{{ $id }}' ? 'bg-indigo-500/15 text-indigo-700 ring-1 ring-indigo-500/25 dark:text-indigo-300' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
                        >{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Main form column --}}
            <div class="space-y-5 lg:col-span-8 xl:col-span-8">
                @include('admin.servers.partials._provision-form')
            </div>

            {{-- Sticky sidebar --}}
            <aside class="lg:col-span-4 xl:col-span-4">
                <div class="infra-provision-sidebar space-y-4 p-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('Infrastructure insights') }}</p>
                        <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Provisioning readiness') }}</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach (['health' => __('Health score'), 'readiness' => __('Deployment readiness'), 'security' => __('Security risk (inv.)'), 'connectivity' => __('Connectivity')] as $key => $label)
                            <div>
                                <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500">
                                    <span>{{ $label }}</span>
                                    <span class="tabular-nums text-indigo-600 dark:text-indigo-400" x-text="scores.{{ $key }} + '%'"></span>
                                </div>
                                <div class="infra-provision-meter">
                                    <div class="infra-provision-meter-fill" :style="'width:' + scores.{{ $key }} + '%'"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <dt class="text-slate-500">{{ __('Est. monthly cost') }}</dt>
                            <dd class="mt-1 font-semibold tabular-nums text-slate-900 dark:text-white">
                                <span x-text="form.currency || 'KES'"></span> <span x-text="estimatedCost">0.00</span>
                            </dd>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <dt class="text-slate-500">{{ __('SSL status') }}</dt>
                            <dd class="mt-1 truncate font-semibold text-slate-900 dark:text-white" x-text="form.ssl_status || '—'"></dd>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <dt class="text-slate-500">{{ __('Backup') }}</dt>
                            <dd class="mt-1 truncate font-semibold text-slate-900 dark:text-white" x-text="form.backup_status || '—'"></dd>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <dt class="text-slate-500">{{ __('Provider') }}</dt>
                            <dd class="mt-1 truncate font-semibold text-slate-900 dark:text-white" x-text="provider || '—'"></dd>
                        </div>
                    </dl>

                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Checklist') }} (<span x-text="checklistComplete"></span>/6)</p>
                        <ul class="space-y-2">
                            <template x-for="item in checklist" :key="item.label">
                                <li class="flex items-start gap-2 text-xs">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full" :class="item.done ? 'bg-emerald-500/20 text-emerald-600' : 'bg-slate-200 text-slate-400 dark:bg-slate-700'">
                                        <span x-show="item.done">✓</span>
                                    </span>
                                    <span :class="item.done ? 'text-slate-700 dark:text-slate-200' : 'text-slate-500'" x-text="item.label"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="hidden space-y-2 border-t border-slate-200/80 pt-4 dark:border-slate-800 lg:block">
                        <button type="submit" class="flex w-full min-h-[48px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 via-indigo-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            {{ __('Register server') }}
                        </button>
                        <button type="button" @click="testConnection()" class="flex w-full min-h-[44px] items-center justify-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            {{ __('Test connection') }}
                        </button>
                        <button type="button" @click="scrollToSection('identity')" class="flex w-full min-h-[44px] items-center justify-center rounded-xl text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ __('Validate infrastructure') }}
                        </button>
                    </div>
                </div>
            </aside>
        </form>

        {{-- Mobile sticky actions --}}
        <div class="infra-provision-mobile-bar">
            <div class="mx-auto flex max-w-lg gap-2">
                <button type="button" @click="saveDraft()" class="flex min-h-[48px] flex-1 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    {{ __('Draft') }}
                </button>
                <button type="submit" form="server-provision-form" class="flex min-h-[48px] flex-[2] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 via-indigo-600 to-violet-600 text-sm font-semibold text-white shadow-lg">
                    {{ __('Register server') }}
                </button>
            </div>
        </div>
    </div>
</x-dashboard-layout>
