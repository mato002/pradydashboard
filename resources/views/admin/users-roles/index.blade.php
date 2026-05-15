@php
    $actionLabels = [
        'view' => __('View'),
        'create' => __('Create'),
        'update' => __('Update'),
        'delete' => __('Delete'),
        'deploy' => __('Deploy'),
        'rollback' => __('Rollback'),
        'export' => __('Export'),
        'manage_billing' => __('Billing'),
        'manage_users' => __('Users'),
        'manage_servers' => __('Servers'),
    ];
@endphp

<x-dashboard-layout :heading="__('Identity & Access')" :subheading="__('Enterprise IAM control center')">
    <div
        x-data="iamCenter(@js($users), @js($roles), @js($permissions))"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Security & governance') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Identity & Access Management') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('Users, roles, permissions, sessions, API tokens, authentication policies, and audit visibility.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-500/20 dark:text-amber-200">
                    {{ __('Threat') }}: {{ ucfirst($securityIntel['threat_level']) }}
                </span>
                <a href="{{ route('users-roles.users.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 hover:brightness-110">
                    {{ __('Add user') }}
                </a>
                <a href="{{ route('users-roles.roles.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white/80 px-4 py-2 text-xs font-semibold backdrop-blur dark:border-slate-700 dark:bg-slate-900/80">
                    {{ __('Create role') }}
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-8">
            @foreach ([
                'total_users' => ['icon' => 'users', 'title' => __('Total users')],
                'active_sessions' => ['icon' => 'sessions', 'title' => __('Active sessions')],
                'super_admins' => ['icon' => 'admin', 'title' => __('Super admins')],
                'suspended' => ['icon' => 'suspend', 'title' => __('Suspended')],
                'pending_invites' => ['icon' => 'invite', 'title' => __('Pending invites')],
                'api_tokens' => ['icon' => 'token', 'title' => __('API tokens')],
                'mfa_enabled' => ['icon' => 'mfa', 'title' => __('MFA enabled')],
                'failed_logins' => ['icon' => 'fail', 'title' => __('Failed logins')],
            ] as $key => $meta)
                <x-ui.kpi-card
                    :title="$meta['title']"
                    :value="$kpis[$key]['value']"
                    :trend="$kpis[$key]['trend']"
                    :sublabel="$kpis[$key]['sublabel']"
                    :points="$kpis[$key]['points']"
                    :tone="$kpis[$key]['tone']"
                    :animate="is_numeric($kpis[$key]['value'])"
                >
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
                    </x-slot>
                </x-ui.kpi-card>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-12">
            <div class="space-y-5 xl:col-span-9">
                {{-- Tabs --}}
                <div class="overflow-x-auto prady-scrollbar">
                    <div class="flex min-w-max gap-1 rounded-xl border border-slate-200/80 bg-slate-50/80 p-1 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
                        @foreach ([
                            'users' => __('Users'),
                            'roles' => __('Roles'),
                            'permissions' => __('Permissions'),
                            'teams' => __('Teams'),
                            'sessions' => __('Sessions'),
                            'tokens' => __('API Tokens'),
                            'audit' => __('Audit'),
                            'auth' => __('Auth policies'),
                            'alerts' => __('Alerts'),
                        ] as $tab => $label)
                            <button type="button" @click="activeTab = '{{ $tab }}'" :class="activeTab === '{{ $tab }}' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'" class="rounded-lg px-3 py-2 text-[11px] font-semibold whitespace-nowrap transition">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Users tab --}}
                <div x-show="activeTab === 'users'" class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/70">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Platform users') }}</h3>
                                <p class="text-xs text-slate-500" x-text="filteredUsers.length + ' {{ __('users') }}'"></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <input type="search" x-model="searchQuery" placeholder="{{ __('Search name or email…') }}" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-3 text-xs dark:border-slate-700 dark:bg-slate-800" />
                                <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                                    <option value="">{{ __('All statuses') }}</option>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="invited">{{ __('Invited') }}</option>
                                    <option value="suspended">{{ __('Suspended') }}</option>
                                </select>
                                <select x-model="filterRisk" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                                    <option value="">{{ __('All risk') }}</option>
                                    <option value="high">{{ __('High risk') }}</option>
                                    <option value="medium">{{ __('Medium') }}</option>
                                    <option value="low">{{ __('Low') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="prady-scrollbar overflow-x-auto">
                            <table class="prady-table w-full min-w-[1100px]">
                                <thead>
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Roles') }}</th>
                                        <th>{{ __('Access') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Last activity') }}</th>
                                        <th>{{ __('IP') }}</th>
                                        <th>{{ __('MFA') }}</th>
                                        <th>{{ __('Sessions') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    <template x-for="user in filteredUsers" :key="user.id">
                                        <tr class="transition hover:bg-indigo-50/40 dark:hover:bg-indigo-500/5">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="relative">
                                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white" x-text="user.initials"></span>
                                                        <span x-show="user.online" class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-slate-900 dark:text-white" x-text="user.name"></p>
                                                        <p class="text-[11px] text-slate-500" x-text="user.email"></p>
                                                    </div>
                                                    <span x-show="user.risk === 'high'" class="rounded bg-rose-500/15 px-1.5 py-0.5 text-[9px] font-bold uppercase text-rose-600">{{ __('Risk') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-400" x-text="user.department"></td>
                                            <td>
                                                <template x-for="role in user.roles" :key="role">
                                                    <span class="mr-1 inline-flex rounded-md bg-indigo-500/10 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:text-indigo-300" x-text="role"></span>
                                                </template>
                                            </td>
                                            <td class="text-xs font-medium capitalize text-slate-600" x-text="user.access_level"></td>
                                            <td>
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ring-1 ring-inset"
                                                    :class="{
                                                        'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': user.status === 'active',
                                                        'bg-amber-500/12 text-amber-800 ring-amber-500/20': user.status === 'invited',
                                                        'bg-rose-500/12 text-rose-700 ring-rose-500/20': user.status === 'suspended',
                                                    }" x-text="user.status"></span>
                                            </td>
                                            <td class="text-xs text-slate-500" x-text="user.last_activity"></td>
                                            <td class="font-mono text-[11px] text-slate-500" x-text="user.last_ip"></td>
                                            <td>
                                                <span x-show="user.mfa" class="text-emerald-600">✓</span>
                                                <span x-show="!user.mfa" class="text-rose-500">✗</span>
                                            </td>
                                            <td class="tabular-nums text-sm font-medium" x-text="user.sessions"></td>
                                            <td class="text-right">
                                                <a :href="'{{ url('users-roles/users') }}/' + user.id" @click.stop class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Roles tab --}}
                <div x-show="activeTab === 'roles'" x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($roles as $role)
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card backdrop-blur transition hover:shadow-card-hover dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ $role['name'] }}</h4>
                                    <p class="mt-1 text-xs text-slate-500">{{ $role['description'] }}</p>
                                </div>
                                <span class="rounded-full bg-violet-500/10 px-2 py-0.5 text-[10px] font-bold text-violet-700 dark:text-violet-300">L{{ $role['level'] }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800"><p class="font-bold text-slate-900 dark:text-white">{{ $role['users'] }}</p><p class="text-slate-500">{{ __('Users') }}</p></div>
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800"><p class="font-bold text-indigo-600">{{ $role['permissions'] }}</p><p class="text-slate-500">{{ __('Permissions') }}</p></div>
                            </div>
                            @if ($role['inherits'])
                                <p class="mt-2 text-[10px] text-slate-500">{{ __('Inherits') }}: <span class="font-semibold">{{ $role['inherits'] }}</span></p>
                            @endif
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('users-roles.roles.show', $role['slug']) }}" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-[11px] font-semibold dark:border-slate-700">{{ __('View') }}</a>
                                <a href="{{ route('users-roles.roles.edit', $role['slug']) }}" class="flex-1 rounded-lg bg-indigo-600 py-1.5 text-center text-[11px] font-semibold text-white">{{ __('Edit') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Permissions matrix --}}
                <div x-show="activeTab === 'permissions'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Permissions matrix') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Role: Super Admin — inherited grants shown') }}</p>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto p-4">
                        <table class="w-full min-w-[800px] text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-2 text-left font-semibold text-slate-600">{{ __('Module') }}</th>
                                    @foreach ($permissions['actions'] as $action)
                                        <th class="px-1 py-2 text-center font-semibold text-slate-500">{{ $actionLabels[$action] ?? $action }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions['matrix'] as $row)
                                    <tr class="border-b border-slate-100 dark:border-slate-800/80">
                                        <td class="py-2 font-medium text-slate-800 dark:text-slate-200">{{ $row['module'] }}</td>
                                        @foreach ($permissions['actions'] as $action)
                                            <td class="px-1 py-2 text-center">
                                                @if ($row['grants'][$action] ?? false)
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/15 text-emerald-600">✓</span>
                                                @else
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-300 dark:bg-slate-800">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Teams --}}
                <div x-show="activeTab === 'teams'" x-cloak class="space-y-3">
                    @foreach ($teams as $team)
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ $team['name'] }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Lead') }}: {{ $team['lead'] }} · {{ $team['members'] }} {{ __('members') }}</p>
                                </div>
                                <span class="rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-300">{{ $team['permissions'] }}</span>
                            </div>
                            @if (count($team['children']) > 0)
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach ($team['children'] as $child)
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] dark:bg-slate-800">{{ $child }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Sessions --}}
                <div x-show="activeTab === 'sessions'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Active sessions') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($sessions as $session)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $session['user'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $session['device'] }} · {{ $session['browser'] }} · {{ $session['os'] }}</p>
                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $session['ip'] }} — {{ $session['location'] }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-500">{{ $session['started'] }}</span>
                                    <x-ui.status-badge :variant="$session['status'] === 'active' ? 'success' : 'neutral'">{{ ucfirst($session['status']) }}</x-ui.status-badge>
                                    <button type="button" class="rounded-lg border border-rose-200 px-2 py-1 text-[10px] font-semibold text-rose-600 dark:border-rose-500/30">{{ __('Terminate') }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- API Tokens --}}
                <div x-show="activeTab === 'tokens'" x-cloak class="space-y-3">
                    @foreach ($apiTokens as $token)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $token['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $token['owner'] }} · {{ $token['scopes'] }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">{{ __('Last used') }}: {{ $token['last_used'] }} · {{ $token['requests'] }} {{ __('requests') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.status-badge :variant="$token['status'] === 'active' ? 'success' : 'danger'">{{ ucfirst($token['status']) }}</x-ui.status-badge>
                                <span class="text-xs text-slate-500">{{ $token['expires'] }}</span>
                                <button type="button" class="text-xs font-semibold text-indigo-600">{{ __('Rotate') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Audit --}}
                <div x-show="activeTab === 'audit'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Audit timeline') }}</h3>
                        <button type="button" class="text-xs font-semibold text-indigo-600">{{ __('Export report') }}</button>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($auditLogs as $log)
                            <li class="flex gap-4 px-4 py-3">
                                <span class="w-12 shrink-0 font-mono text-xs text-slate-400">{{ $log['time'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $log['action'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $log['actor'] }} → {{ $log['target'] }}</p>
                                </div>
                                <x-ui.status-badge :variant="$log['severity'] === 'danger' ? 'danger' : ($log['severity'] === 'warning' ? 'warning' : 'info')">{{ $log['type'] }}</x-ui.status-badge>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Auth policies --}}
                <div x-show="activeTab === 'auth'" x-cloak class="grid gap-4 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Authentication policies') }}</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach ($authPolicies['policies'] as $policy)
                                <li class="flex items-center justify-between rounded-xl border border-slate-200/80 px-3 py-2 dark:border-slate-700">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $policy['name'] }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $policy['scope'] }}</p>
                                    </div>
                                    <span @class(['h-2 w-2 rounded-full', 'bg-emerald-500' => $policy['enabled'], 'bg-slate-300 dark:bg-slate-600' => ! $policy['enabled']])></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-rose-200/60 bg-rose-50/30 p-4 dark:border-rose-500/20 dark:bg-rose-950/20">
                        <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-200">{{ __('Suspicious activity') }}</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach ($authPolicies['suspicious'] as $item)
                                <li class="rounded-xl bg-white/80 px-3 py-2 text-xs dark:bg-slate-900/60">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $item['user'] }}</p>
                                    <p class="text-slate-500">{{ $item['reason'] }} — {{ $item['ip'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $item['time'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Security alerts --}}
                <div x-show="activeTab === 'alerts'" x-cloak class="space-y-2">
                    @foreach ($securityAlerts as $alert)
                        <div @class([
                            'rounded-2xl border px-4 py-3',
                            'border-rose-200/80 bg-rose-50/50 dark:border-rose-500/20 dark:bg-rose-950/30' => $alert['type'] === 'danger',
                            'border-amber-200/80 bg-amber-50/40 dark:border-amber-500/20 dark:bg-amber-950/20' => $alert['type'] === 'warning',
                            'border-sky-200/80 bg-sky-50/40 dark:border-sky-500/20 dark:bg-sky-950/20' => $alert['type'] === 'info',
                        ])>
                            <div class="flex justify-between gap-2">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                <span class="text-[10px] text-slate-400">{{ $alert['time'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $alert['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right sidebar: security intelligence --}}
            <div class="space-y-4 xl:col-span-3">
                <div class="sticky top-4 space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-50/90 to-violet-50/50 p-4 shadow-lg backdrop-blur-xl dark:border-indigo-500/20 dark:from-indigo-950/50 dark:to-violet-950/30">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ __('Security intelligence') }}</p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <p class="text-xs text-slate-500">{{ __('Threat level') }}</p>
                                <p class="text-2xl font-bold text-amber-600">{{ $securityIntel['threat_label'] }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="rounded-xl bg-white/60 p-2 dark:bg-slate-900/50">
                                    <p class="text-lg font-bold text-emerald-600">{{ $securityIntel['login_success_rate'] }}%</p>
                                    <p class="text-[10px] text-slate-500">{{ __('Login success') }}</p>
                                </div>
                                <div class="rounded-xl bg-white/60 p-2 dark:bg-slate-900/50">
                                    <p class="text-lg font-bold text-indigo-600">{{ $securityIntel['mfa_adoption'] }}%</p>
                                    <p class="text-[10px] text-slate-500">{{ __('MFA adoption') }}</p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-white/60 p-3 dark:bg-slate-900/50">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">{{ __('Security score') }}</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $securityIntel['security_score'] }}/100</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-indigo-500" style="width: {{ $securityIntel['security_score'] }}%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">{{ __('Open incidents') }}</span>
                                <span class="font-bold text-rose-600">{{ $securityIntel['open_incidents'] }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">{{ __('Access requests') }}</span>
                                <span class="font-bold text-amber-600">{{ $securityIntel['pending_access_requests'] }} {{ __('pending') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Quick actions') }}</h4>
                        <div class="mt-3 grid gap-2">
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Create role') }}</button>
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Generate API token') }}</button>
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Force logout all') }}</button>
                            <button type="button" class="w-full rounded-xl bg-indigo-600 py-2 text-xs font-semibold text-white hover:brightness-110">{{ __('Run security scan') }}</button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-4 backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Advanced governance') }}</h4>
                        <ul class="mt-2 space-y-1.5 text-[11px] text-slate-600 dark:text-slate-400">
                            <li>· {{ __('Access approval workflows') }}</li>
                            <li>· {{ __('Temporary access grants') }}</li>
                            <li>· {{ __('Break-glass accounts') }}</li>
                            <li>· {{ __('Delegated administration') }}</li>
                            <li>· {{ __('Role expiration dates') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
