@php
    $priorityVariant = fn (string $p): string => match ($p) {
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        default => 'neutral',
    };

    $statusVariant = fn (string $s): string => match ($s) {
        'open' => 'info',
        'pending' => 'neutral',
        'escalated' => 'danger',
        'in_progress' => 'warning',
        'resolved' => 'success',
        'closed' => 'neutral',
        default => 'neutral',
    };

    $slaVariant = fn (string $s): string => match ($s) {
        'breached' => 'danger',
        'at_risk' => 'warning',
        default => 'success',
    };

    $incidentTypeLabel = fn (string $t): string => match ($t) {
        'server_outage' => __('Server outage'),
        'ssl_failure' => __('SSL failure'),
        'backup_failure' => __('Backup failure'),
        'api_downtime' => __('API downtime'),
        'deployment_failure' => __('Deployment failure'),
        default => ucfirst(str_replace('_', ' ', $t)),
    };

    $resolutionMax = max(collect($analytics['resolution_trend'])->max('value') ?? 1, 1);
@endphp

<x-dashboard-layout :heading="__('Support & Incidents')" :subheading="__('Enterprise customer support operations center')">
    <div
        x-data="supportCenter(@js($tickets), @js($incidents), @js($conversations))"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Operations') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Customer Support & Incident Management') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('Tenant tickets, infrastructure incidents, SLA compliance, assignments, and resolution analytics.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    {{ __('Live queue') }}
                </span>
                <a href="{{ route('support-tickets.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('New ticket') }}
                </a>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-orange-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    {{ __('Declare incident') }}
                </button>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Open tickets')" :value="$kpis['open_tickets']['value']" :trend="$kpis['open_tickets']['trend']" :sublabel="$kpis['open_tickets']['sublabel']" :points="$kpis['open_tickets']['points']" :tone="$kpis['open_tickets']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Critical incidents')" :value="$kpis['critical_incidents']['value']" :trend="$kpis['critical_incidents']['trend']" :sublabel="$kpis['critical_incidents']['sublabel']" :points="$kpis['critical_incidents']['points']" :tone="$kpis['critical_incidents']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('SLA breaches')" :value="$kpis['sla_breaches']['value']" :trend="$kpis['sla_breaches']['trend']" :sublabel="$kpis['sla_breaches']['sublabel']" :points="$kpis['sla_breaches']['points']" :tone="$kpis['sla_breaches']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg resolution')" :value="$kpis['avg_resolution']['value']" :animate="false" :trend="$kpis['avg_resolution']['trend']" :sublabel="$kpis['avg_resolution']['sublabel']" :points="$kpis['avg_resolution']['points']" :tone="$kpis['avg_resolution']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Active agents')" :value="$kpis['active_agents']['value']" :trend="$kpis['active_agents']['trend']" :sublabel="$kpis['active_agents']['sublabel']" :points="$kpis['active_agents']['points']" :tone="$kpis['active_agents']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Resolved today')" :value="$kpis['resolved_today']['value']" :trend="$kpis['resolved_today']['trend']" :sublabel="$kpis['resolved_today']['sublabel']" :points="$kpis['resolved_today']['points']" :tone="$kpis['resolved_today']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Main workspace --}}
        <div class="grid gap-5 xl:grid-cols-12">
            {{-- Tickets table --}}
            <div class="xl:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Ticket queue') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="filteredTickets.length + ' {{ __('tickets') }}'"></p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="">{{ __('All statuses') }}</option>
                                <option value="open">{{ __('Open') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="escalated">{{ __('Escalated') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                                <option value="closed">{{ __('Closed') }}</option>
                            </select>
                            <select x-model="filterPriority" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="">{{ __('All priorities') }}</option>
                                <option value="critical">{{ __('Critical') }}</option>
                                <option value="high">{{ __('High') }}</option>
                                <option value="medium">{{ __('Medium') }}</option>
                                <option value="low">{{ __('Low') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table w-full min-w-[900px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Ticket ID') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('SLA') }}</th>
                                    <th>{{ __('Last Response') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <template x-for="ticket in filteredTickets" :key="ticket.id">
                                    <tr
                                        @click="selectTicket(ticket)"
                                        class="cursor-pointer transition hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                                        :class="selectedTicket?.id === ticket.id ? 'bg-indigo-50/80 dark:bg-indigo-500/10' : ''"
                                    >
                                        <td class="font-mono text-xs font-semibold">
                                            <a
                                                :href="'{{ url('support-tickets') }}/' + (ticket.db_id ?? ticket.id)"
                                                @click.stop
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                                x-text="ticket.id"
                                            ></a>
                                        </td>
                                        <td class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="ticket.tenant"></td>
                                        <td class="max-w-[200px] truncate text-sm text-slate-700 dark:text-slate-300" x-text="ticket.subject" :title="ticket.subject"></td>
                                        <td><span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300" x-text="ticket.category"></span></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                                                :class="{
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20 dark:text-rose-300': ticket.priority === 'critical',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20 dark:text-amber-200': ticket.priority === 'high',
                                                    'bg-sky-500/12 text-sky-800 ring-sky-500/20 dark:text-sky-200': ticket.priority === 'medium',
                                                    'bg-slate-500/10 text-slate-600 ring-slate-500/15 dark:text-slate-300': ticket.priority === 'low',
                                                }"
                                                x-text="ticket.priority"
                                            ></span>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400" x-text="ticket.assigned_to"></td>
                                        <td>
                                            <div class="flex min-w-[80px] flex-col gap-1">
                                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                    <div
                                                        class="h-full rounded-full transition-all"
                                                        :class="{
                                                            'bg-rose-500': ticket.sla_status === 'breached',
                                                            'bg-amber-500': ticket.sla_status === 'at_risk',
                                                            'bg-emerald-500': ticket.sla_status === 'on_track',
                                                        }"
                                                        :style="'width:' + ticket.sla_progress + '%'"
                                                    ></div>
                                                </div>
                                                <span class="text-[10px] font-semibold uppercase" :class="{
                                                    'text-rose-600 dark:text-rose-400': ticket.sla_status === 'breached',
                                                    'text-amber-600 dark:text-amber-400': ticket.sla_status === 'at_risk',
                                                    'text-emerald-600 dark:text-emerald-400': ticket.sla_status === 'on_track',
                                                }" x-text="ticket.sla_status.replace('_', ' ')"></span>
                                            </div>
                                        </td>
                                        <td class="text-xs text-slate-500 dark:text-slate-400" x-text="ticket.last_response"></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                                                :class="{
                                                    'bg-sky-500/12 text-sky-800 ring-sky-500/20': ticket.status === 'open',
                                                    'bg-slate-500/10 text-slate-600 ring-slate-500/15': ticket.status === 'pending' || ticket.status === 'closed',
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20': ticket.status === 'escalated',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20': ticket.status === 'in_progress',
                                                    'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': ticket.status === 'resolved',
                                                }"
                                                x-text="ticket.status.replace('_', ' ')"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Communication panel --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Conversation') }}</h3>
                            <p class="text-xs text-slate-500" x-show="selectedTicket" x-text="selectedTicket?.id + ' — ' + selectedTicket?.subject"></p>
                            <p class="text-xs text-slate-500" x-show="!selectedTicket">{{ __('Select a ticket to view thread') }}</p>
                        </div>
                        <div class="flex gap-1 rounded-lg bg-slate-100 p-0.5 dark:bg-slate-800">
                            <button type="button" @click="convTab = 'thread'" :class="convTab === 'thread' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300">{{ __('Thread') }}</button>
                            <button type="button" @click="convTab = 'internal'" :class="convTab === 'internal' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300">{{ __('Internal') }}</button>
                            <button type="button" @click="convTab = 'audit'" :class="convTab === 'audit' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300">{{ __('Audit') }}</button>
                        </div>
                    </div>
                    <div class="max-h-80 space-y-3 overflow-y-auto p-4 prady-scrollbar">
                        <template x-if="!selectedTicket">
                            <p class="py-8 text-center text-sm text-slate-500">{{ __('Click any ticket row to load communication history.') }}</p>
                        </template>
                        <template x-for="msg in activeMessages" :key="msg.time + msg.author">
                            <div
                                class="flex gap-3"
                                :class="msg.type === 'customer' ? '' : msg.type === 'internal' ? 'opacity-90' : ''"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                                    :class="{
                                        'bg-indigo-500': msg.type === 'customer',
                                        'bg-violet-500': msg.type === 'agent',
                                        'bg-amber-500': msg.type === 'internal',
                                        'bg-slate-500': msg.type === 'system',
                                    }"
                                    x-text="msg.author.charAt(0)"
                                ></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200" x-text="msg.author"></span>
                                        <span class="text-[10px] text-slate-400" x-text="msg.time"></span>
                                        <span x-show="msg.type === 'internal'" class="rounded bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-bold uppercase text-amber-700 dark:text-amber-300">{{ __('Internal') }}</span>
                                    </div>
                                    <p class="mt-1 rounded-xl rounded-tl-sm bg-slate-100 px-3 py-2 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300" x-text="msg.body"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedTicket && activeMessages.length === 0">
                            <p class="py-6 text-center text-sm text-slate-500">{{ __('No messages in this view.') }}</p>
                        </template>
                    </div>
                    <div class="border-t border-slate-200/80 p-3 dark:border-slate-800/80">
                        <div class="flex gap-2">
                            <input type="text" placeholder="{{ __('Reply to customer…') }}" class="flex-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" disabled />
                            <button type="button" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white opacity-60" disabled>{{ __('Send') }}</button>
                        </div>
                        <p class="mt-2 flex flex-wrap gap-3 text-[10px] text-slate-400">
                            <span>📎 {{ __('Attachments') }}</span>
                            <span>📝 {{ __('Canned responses') }}</span>
                            <span>🔒 {{ __('Internal note') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right column: incidents + SLA + agents --}}
            <div class="xl:col-span-4 space-y-4">
                {{-- Critical incidents --}}
                <div class="overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-b from-rose-50/80 to-white shadow-card dark:border-rose-500/20 dark:from-rose-950/30 dark:to-slate-900/60">
                    <div class="flex items-center justify-between border-b border-rose-200/50 px-4 py-3 dark:border-rose-500/20">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-rose-900 dark:text-rose-200">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg>
                            {{ __('Critical incidents') }}
                        </h3>
                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300">{{ count($incidents) }} {{ __('active') }}</span>
                    </div>
                    <div class="max-h-[420px] space-y-3 overflow-y-auto p-3 prady-scrollbar">
                        @foreach ($incidents as $incident)
                            <div
                                x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                                class="rounded-xl border border-slate-200/80 bg-white/90 p-3 dark:border-slate-700 dark:bg-slate-900/80"
                            >
                                <button type="button" @click="open = !open" class="w-full text-left">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-mono text-[10px] font-bold text-rose-600 dark:text-rose-400">{{ $incident['id'] }}</p>
                                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $incident['title'] }}</p>
                                            <p class="mt-1 text-[11px] text-slate-500">{{ $incidentTypeLabel($incident['type']) }} · {{ $incident['started'] }}</p>
                                        </div>
                                        <x-ui.status-badge :variant="$incident['severity'] === 'critical' ? 'danger' : ($incident['severity'] === 'high' ? 'warning' : 'info')">
                                            {{ ucfirst($incident['severity']) }}
                                        </x-ui.status-badge>
                                    </div>
                                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                        <div>
                                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $incident['affected_tenants'] }}</p>
                                            <p class="text-[10px] uppercase text-slate-500">{{ __('Tenants') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-bold text-amber-600">{{ $incident['escalation_level'] }}</p>
                                            <p class="text-[10px] uppercase text-slate-500">{{ __('Escalation') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-bold text-emerald-600">{{ $incident['recovery'] }}%</p>
                                            <p class="text-[10px] uppercase text-slate-500">{{ __('Recovery') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                        <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-emerald-500" style="width: {{ $incident['recovery'] }}%"></div>
                                    </div>
                                </button>
                                <div x-show="open" x-transition class="mt-3 border-t border-slate-200/80 pt-3 dark:border-slate-700">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Timeline') }}</p>
                                    <ul class="space-y-2">
                                        @foreach ($incident['timeline'] as $event)
                                            <li class="flex gap-2 text-xs">
                                                <span class="shrink-0 font-mono text-slate-400">{{ $event['time'] }}</span>
                                                <span class="text-slate-600 dark:text-slate-400">{{ $event['event'] }} <span class="text-slate-400">— {{ $event['actor'] }}</span></span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- SLA management --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('SLA management') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Compliance') }}: <span class="font-semibold text-emerald-600">{{ $slaOverview['compliance_pct'] }}%</span></p>
                    </div>
                    <div class="space-y-4 p-4">
                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $slaOverview['response_target'] }}</p>
                                <p class="text-slate-500">{{ __('Response target') }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $slaOverview['resolution_target'] }}</p>
                                <p class="text-slate-500">{{ __('Resolution target') }}</p>
                            </div>
                        </div>
                        @foreach ($slaOverview['timers'] as $timer)
                            <div>
                                <div class="mb-1 flex justify-between text-xs">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $timer['label'] }}</span>
                                    <span class="font-mono font-semibold" :class="'{{ $timer['status'] }}' === 'at_risk' ? 'text-amber-600' : 'text-emerald-600'">{{ $timer['remaining'] }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div @class([
                                        'h-full rounded-full',
                                        'bg-amber-500' => $timer['status'] === 'at_risk',
                                        'bg-emerald-500' => $timer['status'] === 'on_track',
                                    ]) style="width: {{ $timer['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('At risk / overdue') }}</p>
                        <ul class="space-y-1.5">
                            @foreach ($slaOverview['overdue'] as $ticket)
                                <li class="flex items-center justify-between rounded-lg bg-rose-50/50 px-2 py-1.5 text-xs dark:bg-rose-500/10">
                                    <span class="font-mono text-rose-700 dark:text-rose-300">{{ $ticket['id'] }}</span>
                                    <x-ui.status-badge :variant="$slaVariant($ticket['sla_status'])">{{ str_replace('_', ' ', $ticket['sla_status']) }}</x-ui.status-badge>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Agent performance --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Agent performance') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($agents as $agent)
                            <li class="flex items-center gap-3 px-4 py-3">
                                <div class="relative">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                                        {{ strtoupper(substr($agent['name'], 0, 1)) }}
                                    </div>
                                    <span @class([
                                        'absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-slate-900',
                                        'bg-emerald-500' => $agent['status'] === 'online',
                                        'bg-amber-400' => $agent['status'] === 'away',
                                    ])></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $agent['name'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $agent['role'] }}</p>
                                </div>
                                <div class="text-right text-xs">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $agent['tickets'] }} {{ __('open') }}</p>
                                    <p class="text-emerald-600">{{ $agent['sla_pct'] }}% SLA</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Analytics + Automation --}}
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Support analytics') }}</h3>
                    </div>
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Resolution trend (7d)') }}</p>
                            <div class="flex h-32 items-end gap-1">
                                @foreach ($analytics['resolution_trend'] as $bar)
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div
                                            class="w-full rounded-t bg-gradient-to-t from-indigo-600 to-violet-500 transition-all hover:opacity-80"
                                            style="height: {{ max(8, ($bar['value'] / $resolutionMax) * 100) }}%"
                                            title="{{ $bar['value'] }}"
                                        ></div>
                                        <span class="text-[10px] text-slate-500">{{ $bar['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('First response distribution') }}</p>
                            @foreach ($analytics['response_times'] as $rt)
                                <div class="mb-2">
                                    <div class="mb-0.5 flex justify-between text-xs">
                                        <span class="text-slate-600 dark:text-slate-400">{{ $rt['label'] }}</span>
                                        <span class="font-semibold tabular-nums">{{ $rt['pct'] }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                        <div class="h-full rounded-full bg-sky-500" style="width: {{ $rt['pct'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-4 flex items-center gap-2 rounded-xl bg-violet-50 p-3 dark:bg-violet-500/10">
                                <span class="text-2xl">⭐</span>
                                <div>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $analytics['satisfaction'] }}/5</p>
                                    <p class="text-xs text-slate-500">{{ __('Tenant satisfaction (CSAT)') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Issue categories') }}</p>
                            <div class="space-y-2">
                                @foreach ($analytics['categories'] as $cat)
                                    <div class="flex items-center gap-3">
                                        <span class="w-28 shrink-0 text-xs font-medium text-slate-700 dark:text-slate-300">{{ $cat['name'] }}</span>
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ $cat['pct'] }}%"></div>
                                        </div>
                                        <span class="w-8 text-right text-xs font-semibold tabular-nums text-slate-600">{{ $cat['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="sm:col-span-2 border-t border-slate-200/80 pt-4 dark:border-slate-800/80">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recurring incidents') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($analytics['recurring'] as $rec)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $rec['issue'] }}
                                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold dark:bg-slate-700">{{ $rec['count'] }}</span>
                                        @if ($rec['trend'] === 'up')
                                            <span class="text-rose-500">↑</span>
                                        @elseif ($rec['trend'] === 'down')
                                            <span class="text-emerald-500">↓</span>
                                        @else
                                            <span class="text-slate-400">→</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Automation & routing') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Rules engine — operational') }}</p>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($automation as $rule)
                            <li class="flex items-start gap-3 px-4 py-3">
                                <div @class([
                                    'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                    'bg-emerald-500/15 text-emerald-600' => $rule['enabled'],
                                    'bg-slate-500/10 text-slate-400' => ! $rule['enabled'],
                                ])>
                                    @if ($rule['enabled'])
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $rule['name'] }}</p>
                                        <span class="shrink-0 rounded-full bg-indigo-500/10 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-300">{{ number_format($rule['runs']) }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $rule['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

</x-dashboard-layout>
