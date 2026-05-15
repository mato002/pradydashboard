@php
    $levelVariant = fn (?string $l): string => match ($l) {
        'suspended', 'terminated' => 'danger',
        'restricted' => 'warning',
        'warning' => 'info',
        'soft_reminder' => 'neutral',
        default => 'neutral',
    };

    $enforcementVariant = fn (string $s): string => match (true) {
        str_contains($s, 'Enforcing') => 'danger',
        str_contains($s, 'Active') => 'warning',
        str_contains($s, 'Monitoring') => 'info',
        default => 'neutral',
    };

    $severityRing = fn (string $s): string => match ($s) {
        'critical' => 'border-rose-500/30 bg-rose-500/10',
        'warning' => 'border-amber-500/30 bg-amber-500/10',
        default => 'border-sky-500/25 bg-sky-500/10',
    };

    $trendMax = max(collect($restrictionTrends)->max('count') ?? 0, 1);
    $failedMax = max(collect($securityAnalytics['failed_logins'])->max() ?? 0, 1);

    $areaChart = function (array $points, string $stroke, string $fill, int $w = 200, int $h = 48): string {
        $pts = collect($points)->values()->map(fn ($v) => (float) $v)->all();
        if (count($pts) < 2) {
            $pts = [30, 45, 40, 55, 50, 60];
        }
        $min = min($pts);
        $max = max($pts);
        $range = max(1e-6, $max - $min);
        $linePts = [];
        foreach ($pts as $i => $v) {
            $x = ($i / (count($pts) - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 4) - 2;
            $linePts[] = round($x, 1).','.round($y, 1);
        }
        $line = implode(' ', $linePts);

        return '<svg class="w-full h-full" viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="none" aria-hidden="true"><polygon points="0,'.$h.' '.$line.' '.$w.','.$h.'" class="'.$fill.'"/><polyline points="'.$line.'" class="'.$stroke.' fill-none" stroke-width="2"/></svg>';
    };
@endphp

<x-dashboard-layout :heading="__('Access Controls')" :subheading="__('Enterprise IAM & tenant enforcement')">
    <div
        x-data="accessGovernance(@js($detailPayload), @js($tenantOptions))"
        class="space-y-6"
    >
        @if (session('status'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-violet-500 shadow-[0_0_10px_rgba(139,92,246,0.8)]"></span>
                    </span>
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Policy engine active') }}</p>
                </div>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Security & Enforcement Center') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Tenant access governance — grace periods, restrictions, module gating, automated enforcement, and audit trails.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showPolicyModal = true" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add Policy') }}
                </button>
                <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    {{ __('View Audit Trail') }}
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Active Policies')" :value="$kpis['active_policies']" :trend="'+3'" :sublabel="__('Currently enforced')" :points="$spark('ac-policies')" tone="indigo">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Restricted Tenants')" :value="$kpis['restricted_tenants']" :sublabel="__('Feature or login limits')" :points="$spark('ac-restricted')" tone="amber">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Suspended Accounts')" :value="$kpis['suspended_accounts']" :sublabel="__('Full lockdown')" :points="$spark('ac-suspended')" tone="rose">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Grace Period')" :value="$kpis['grace_accounts']" :sublabel="__('Escalation window')" :points="$spark('ac-grace')" tone="sky">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Enforcement Events')" :value="$kpis['enforcement_events']" :trend="'+8'" :sublabel="__('Last 7 days')" :points="$spark('ac-events')" tone="violet">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-6 10.5 6M4.5 19.5h15" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Failed Access')" :value="$kpis['failed_access']" :sublabel="__('24h window')" :points="$securityAnalytics['failed_logins']" tone="rose">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.75a9 9 0 1118 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- Policy table + enforcement --}}
            <div class="space-y-5 lg:col-span-8">
                <x-ui.table-panel :title="__('Access policy registry')" :action-href="route('tenants.index')" :action-label="__('All tenants')">
                    <table class="prady-table">
                        <thead>
                            <tr>
                                <th>{{ __('Tenant') }}</th>
                                <th>{{ __('Policy Type') }}</th>
                                <th>{{ __('Restriction Level') }}</th>
                                <th>{{ __('Trigger') }}</th>
                                <th>{{ __('Enforcement') }}</th>
                                <th>{{ __('Expiry') }}</th>
                                <th>{{ __('Last Activity') }}</th>
                                <th class="text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($policies as $policy)
                                <tr
                                    class="cursor-pointer transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                    @click="selectPolicy({{ $policy['tenant_id'] }})"
                                    :class="selectedId === {{ $policy['tenant_id'] }} && 'bg-violet-500/5'"
                                >
                                    <td class="font-semibold text-slate-900 dark:text-white">
                                        <a href="{{ $policy['tenant_url'] }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" @click.stop>{{ $policy['tenant'] }}</a>
                                    </td>
                                    <td><x-ui.status-badge :variant="$levelVariant($policy['level'])">{{ $policy['policy_type'] }}</x-ui.status-badge></td>
                                    <td class="text-xs text-slate-600 dark:text-slate-300">{{ $policy['restriction_level'] }}</td>
                                    <td class="text-xs text-slate-500">{{ $policy['trigger'] }}</td>
                                    <td><x-ui.status-badge :variant="$enforcementVariant($policy['enforcement_status'])">{{ $policy['enforcement_status'] }}</x-ui.status-badge></td>
                                    <td class="text-xs tabular-nums text-slate-500">{{ $policy['expiry'] }}</td>
                                    <td class="text-xs text-slate-500">{{ $policy['last_activity'] }}</td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-1" @click.stop>
                                            <form method="POST" action="{{ route('access-controls.restrict', $policy['tenant_id']) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg px-2 py-1 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-500/30 hover:bg-amber-500/10 dark:text-amber-300">{{ __('Restrict') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('access-controls.unlock', $policy['tenant_id']) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg px-2 py-1 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-500/30 hover:bg-emerald-500/10 dark:text-emerald-300">{{ __('Unlock') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-panel>

                {{-- Enforcement engine --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-5 text-white shadow-2xl ring-1 ring-white/10 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-300">{{ __('Enforcement engine') }}</p>
                    <h3 class="mt-1 text-sm font-semibold">{{ __('Automated controls & manual overrides') }}</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($enforcementControls as $control)
                            <div class="rounded-xl border border-white/10 bg-white/5 p-3 transition hover:border-violet-500/40 hover:bg-white/10">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold">{{ $control['label'] }}</p>
                                    <span class="relative flex h-2 w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-50"></span>
                                        <span class="relative h-2 w-2 rounded-full bg-violet-400"></span>
                                    </span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $control['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-white/10 pt-4">
                        <template x-if="selected">
                            <form method="POST" :action="`{{ url('access-controls/tenants') }}/${selectedId}/suspend`" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold hover:bg-rose-500">{{ __('Suspend Access') }}</button>
                            </form>
                            <form method="POST" :action="`{{ url('access-controls/tenants') }}/${selectedId}/grace`" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold hover:bg-amber-500">{{ __('Enable Grace Period') }}</button>
                            </form>
                            <form method="POST" :action="`{{ url('access-controls/tenants') }}/${selectedId}/unlock`" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold hover:bg-emerald-500">{{ __('Unlock Tenant') }}</button>
                            </form>
                            <form method="POST" :action="`{{ url('access-controls/tenants') }}/${selectedId}/restrict`" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold hover:bg-violet-500">{{ __('Apply Restrictions') }}</button>
                            </form>
                        </template>
                        <p x-show="!selected" class="text-xs text-slate-400">{{ __('Select a tenant from the policy table to apply enforcement actions.') }}</p>
                    </div>
                </div>

                {{-- Module matrix --}}
                <x-ui.table-panel :title="__('Role & module control')">
                    <table class="prady-table text-xs">
                        <thead>
                            <tr>
                                <th>{{ __('Tenant') }}</th>
                                @foreach ($moduleMatrix['keys'] as $key)
                                    <th class="text-center">{{ $moduleMatrix['labels'][$key] ?? $key }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($moduleMatrix['rows'] as $row)
                                <tr>
                                    <td class="font-semibold text-slate-800 dark:text-slate-100">{{ $row['tenant'] }}</td>
                                    @foreach ($moduleMatrix['keys'] as $key)
                                        <td class="text-center">
                                            @if ($row['modules'][$key] ?? false)
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300">✓</span>
                                            @else
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 ring-1 ring-rose-500/20">✕</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-panel>
            </div>

            {{-- Sidebar: grace + timeline + inspector --}}
            <div class="space-y-5 lg:col-span-4">
                {{-- Grace period --}}
                <div class="overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-b from-amber-500/5 to-transparent shadow-card dark:border-amber-500/25 dark:from-amber-500/10">
                    <div class="border-b border-amber-500/15 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-700 dark:text-amber-300">{{ __('Grace period management') }}</p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Overdue escalation') }}</h3>
                    </div>
                    <div class="max-h-64 space-y-2 overflow-y-auto p-3">
                        @forelse ($graceAccounts as $grace)
                            <div class="rounded-xl border px-3 py-2.5 {{ $severityRing($grace['escalation']) }}">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $grace['tenant'] }}</p>
                                    <span class="font-mono text-xs font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ $grace['days_left'] }}d</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">{{ __('Renewal') }}: {{ $grace['renewal'] }} · {{ $grace['currency'] }} {{ number_format((float) $grace['amount'], 0) }}</p>
                                <div class="mt-2 h-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="width: {{ min(100, max(8, ($grace['days_left'] / 14) * 100)) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-slate-500 py-4">{{ __('No tenants currently in grace period.') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Enforcement timeline --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Enforcement timeline') }}</p>
                    <ul class="mt-3 space-y-3">
                        @foreach ($enforcementTimeline as $event)
                            <li class="relative border-l-2 border-violet-500/30 pl-4">
                                <span class="absolute -left-[5px] top-1 h-2 w-2 rounded-full {{ $event['severity'] === 'critical' ? 'bg-rose-500' : ($event['severity'] === 'warning' ? 'bg-amber-500' : 'bg-sky-500') }}"></span>
                                <p class="text-[10px] text-slate-500">{{ $event['time'] }}</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $event['title'] }}</p>
                                <p class="text-xs text-slate-500">{{ $event['body'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Tenant inspector --}}
                <div x-show="selected" x-cloak class="rounded-2xl border border-violet-500/25 bg-slate-950 p-4 text-white ring-1 ring-violet-500/20">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300">{{ __('Policy inspector') }}</p>
                    <h3 class="mt-1 font-semibold" x-text="selected?.tenant"></h3>
                    <dl class="mt-3 space-y-2 text-xs">
                        <div class="flex justify-between"><dt class="text-slate-400">{{ __('Policy') }}</dt><dd class="font-medium" x-text="selected?.policy_type"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">{{ __('Trigger') }}</dt><dd x-text="selected?.trigger"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">{{ __('Status') }}</dt><dd x-text="selected?.enforcement_status"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">{{ __('Grace left') }}</dt><dd class="tabular-nums" x-text="(selected?.grace_days_left ?? 0) + ' days'"></dd></div>
                    </dl>
                    <template x-if="selected?.disabled_modules?.length">
                        <div class="mt-3">
                            <p class="text-[10px] uppercase text-slate-400">{{ __('Disabled modules') }}</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <template x-for="m in selected.disabled_modules" :key="m">
                                    <span class="rounded-full bg-rose-500/20 px-2 py-0.5 text-[10px] text-rose-200" x-text="m"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                    <a :href="selected?.tenant_url" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-violet-600 py-2 text-xs font-semibold hover:bg-violet-500">{{ __('Open tenant record') }}</a>
                </div>
            </div>
        </div>

        {{-- Security analytics --}}
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">{{ __('Failed login attempts') }}</p>
                <div class="mt-3 h-24">{!! $areaChart($securityAnalytics['failed_logins'], 'stroke-rose-500', 'fill-rose-500/15') !!}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">{{ __('Policy violations') }}</p>
                <div class="mt-3 h-24">{!! $areaChart($securityAnalytics['violations'], 'stroke-amber-500', 'fill-amber-500/15') !!}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Restriction trends') }}</p>
                <div class="mt-4 flex h-24 items-end gap-2">
                    @foreach ($restrictionTrends as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-gradient-to-t from-violet-600 to-indigo-400" style="height: {{ max(10, ($day['count'] / $trendMax) * 100) }}%"></div>
                            <span class="text-[10px] text-slate-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Audit history --}}
        <x-ui.table-panel :title="__('Audit history')">
            <table class="prady-table">
                <thead>
                    <tr>
                        <th>{{ __('Tenant') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('Actor') }}</th>
                        <th>{{ __('When') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach ($auditHistory as $entry)
                        <tr>
                            <td class="font-medium text-slate-900 dark:text-white">{{ $entry['tenant'] }}</td>
                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $entry['action'] }}</td>
                            <td class="text-xs text-slate-500">{{ $entry['actor'] }}</td>
                            <td class="text-xs text-slate-500">{{ $entry['at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.table-panel>

        {{-- Add policy modal --}}
        <div x-show="showPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @keydown.escape.window="showPolicyModal = false">
            <div @click.outside="showPolicyModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Add access policy') }}</h3>
                <form method="POST" action="{{ route('access-controls.policies.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Tenant') }}</label>
                        <select name="tenant_id" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Restriction level') }}</label>
                        <select name="level" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            <option value="soft_reminder">{{ __('Soft reminder') }}</option>
                            <option value="warning">{{ __('Warning / grace') }}</option>
                            <option value="restricted">{{ __('Restricted') }}</option>
                            <option value="suspended">{{ __('Suspended') }}</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="restrict_login" value="1" class="rounded border-slate-300 text-violet-600">
                        {{ __('Disable login') }}
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showPolicyModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('Cancel') }}</button>
                        <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Apply Policy') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-layout>


