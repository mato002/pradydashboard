@php
    use Illuminate\Support\Str;

    $statusVariant = fn (string $s): string => match ($s) {
        'active', 'live', 'success' => 'success',
        'maintenance', 'building', 'deploying', 'pending' => 'warning',
        'suspended', 'failed' => 'danger',
        default => 'info',
    };
@endphp

<x-dashboard-layout :heading="$project->name" :subheading="$project->domain">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-lg font-bold text-white shadow-lg shadow-cyan-500/30">
                {{ mb_strtoupper(mb_substr($project->name, 0, 2)) }}
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.status-badge :variant="$statusVariant($project->status)">{{ $project->status }}</x-ui.status-badge>
                    <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">{{ $meta['environment'] }}</span>
                    <span class="font-mono text-xs text-slate-500">{{ $meta['version'] }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $project->server?->name ?? __('Unassigned server') }} · {{ $project->tenants->count() }} {{ __('tenants') }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/25">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                {{ __('Deploy') }}
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Rollback') }}</button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Restart') }}</button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('View Logs') }}</button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-violet-200/80 bg-violet-50 px-4 py-2 text-xs font-semibold text-violet-800 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-200">{{ __('Scale') }}</button>
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center rounded-xl border border-slate-200/80 px-4 py-2 text-xs font-semibold dark:border-slate-700">{{ __('Edit') }}</a>
        </div>
    </div>

    <x-admin.assigned-staff :assignments="$staffAssignments" class="mb-6" />

    <x-admin.risk-cards :risks="$operationalRisks" class="mb-6" :compact="true" />

    @include('admin.projects.partials.support-summary')

    <x-admin.activity-feed :logs="$activityLogs" class="mb-6" />

    {{-- Health metrics --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
        @foreach ([
            ['label' => __('Uptime'), 'value' => number_format($meta['uptime_pct'], 2).'%', 'tone' => 'emerald'],
            ['label' => __('Response'), 'value' => $meta['response_ms'].'ms', 'tone' => 'sky'],
            ['label' => __('Error rate'), 'value' => $meta['error_rate'].'%', 'tone' => 'rose'],
            ['label' => __('SSL'), 'value' => ucfirst($meta['ssl_health']), 'tone' => 'indigo'],
            ['label' => __('Bandwidth'), 'value' => $meta['bandwidth_gb'].' GB', 'tone' => 'violet'],
            ['label' => __('Storage'), 'value' => $meta['storage_pct'].'%', 'tone' => 'amber'],
        ] as $metric)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        {{-- Pipeline + deployment history --}}
        <div class="space-y-5 lg:col-span-7">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Deployment pipeline') }}</h3>
                </div>
                <div class="flex items-center gap-2 px-4 py-6">
                    @foreach ($pipeline as $i => $stage)
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div @class([
                                'flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold ring-2',
                                'bg-cyan-500 text-white ring-cyan-400 shadow-lg shadow-cyan-500/40 animate-pulse' => $stage['status'] === 'active',
                                'bg-emerald-500 text-white ring-emerald-400' => $stage['status'] === 'done',
                                'bg-rose-500 text-white ring-rose-400' => $stage['status'] === 'failed',
                                'bg-slate-100 text-slate-400 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700' => $stage['status'] === 'pending',
                            ])>{{ $i + 1 }}</div>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</span>
                        </div>
                        @if (! $loop->last)
                            <div class="h-0.5 flex-1 rounded bg-slate-200 dark:bg-slate-700"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <x-ui.table-panel :title="__('Deployment history')">
                <table class="prady-table">
                    <thead>
                        <tr>
                            <th>{{ __('Version') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Environment') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Deployed') }}</th>
                            <th>{{ __('By') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($deploymentHistory as $dep)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $dep['version'] }}</td>
                                <td><x-ui.status-badge :variant="$dep['status'] === 'success' ? 'success' : 'danger'">{{ $dep['status'] }}</x-ui.status-badge></td>
                                <td class="capitalize text-xs">{{ $dep['environment'] }}</td>
                                <td class="tabular-nums text-xs">{{ $dep['duration_sec'] }}s</td>
                                <td class="text-xs text-slate-500">{{ $dep['deployed_at']->diffForHumans() }}</td>
                                <td class="text-xs text-slate-500">{{ $dep['triggered_by'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.table-panel>

            <div class="overflow-hidden rounded-2xl border border-slate-950 bg-slate-950 shadow-card">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-2">
                    <span class="text-xs font-semibold text-slate-300">{{ __('Build logs') }}</span>
                    <span class="font-mono text-[10px] text-cyan-400">{{ $meta['version'] }}</span>
                </div>
                <pre class="max-h-48 overflow-auto p-4 font-mono text-[11px] leading-relaxed text-emerald-400/90">@foreach ($buildLogs as $line){{ $line }}
@endforeach</pre>
            </div>
        </div>

        {{-- Side panels --}}
        <div class="space-y-5 lg:col-span-5">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('DNS & SSL') }}</h3>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">A</dt><dd class="font-mono text-slate-700 dark:text-slate-200">{{ $project->server?->ip_address ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">CNAME</dt><dd class="font-mono">{{ $project->domain }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('SSL') }}</dt><dd class="font-semibold capitalize">{{ $meta['ssl_health'] }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Environment variables') }}</h3>
                <ul class="mt-3 space-y-2">
                    @foreach ($envVars as $var)
                        <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2 font-mono text-[11px] dark:bg-slate-800/80">
                            <span class="text-cyan-600 dark:text-cyan-400">{{ $var['key'] }}</span>
                            <span class="truncate text-slate-600 dark:text-slate-300">{{ $var['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('License API token') }}</h3>
                <p class="mt-1 text-[11px] text-slate-500">{{ __('Bearer token for tenant license checks') }}</p>
                <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-950 p-3 text-[10px] text-slate-300">{{ Str::limit($project->api_token, 48) }}…</pre>
                <form method="post" action="{{ route('projects.regenerate-token', $project) }}" class="mt-3" onsubmit="return confirm('{{ __('Regenerate token?') }}');">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Regenerate token') }}</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Active tenants') }}</h3>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($project->tenants as $tenant)
                        <li class="flex items-center justify-between px-4 py-3 text-sm">
                            <a href="{{ route('tenants.show', $tenant) }}" class="font-medium text-indigo-600 dark:text-indigo-400">{{ $tenant->company_name }}</a>
                            <x-ui.status-badge :variant="$statusVariant($tenant->status)">{{ $tenant->status }}</x-ui.status-badge>
                        </li>
                    @empty
                        <li class="px-4 py-4 text-sm text-slate-500">{{ __('No tenants linked.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">{{ __('Product operations') }}</h3>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('MRR (subscriptions)') }}</dt><dd class="font-semibold">KES {{ number_format($mrr, 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Business model') }}</dt><dd>{{ $project->business_model ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Deployment type') }}</dt><dd>{{ $project->deployment_type ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">{{ __('Tenant rollout summary') }}</h3>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Total deployments') }}</dt><dd class="text-lg font-semibold tabular-nums">{{ $rolloutSummary['total'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Latest') }}</dt><dd class="text-lg font-semibold tabular-nums text-emerald-600">{{ $rolloutSummary['latest'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Outdated') }}</dt><dd class="text-lg font-semibold tabular-nums text-amber-600">{{ $rolloutSummary['outdated'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Critical update required') }}</dt><dd class="text-lg font-semibold tabular-nums text-rose-600">{{ $rolloutSummary['critical_update_required'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Unknown') }}</dt><dd class="text-lg font-semibold tabular-nums">{{ $rolloutSummary['unknown'] }}</dd></div>
            </dl>
            @if ($rolloutSummary['project_current_version'])
                <p class="mt-2 text-xs text-slate-500">{{ __('Project current version') }}: <span class="font-mono">{{ $rolloutSummary['project_current_version'] }}</span></p>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">{{ __('Missing required contracts') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($missingContracts as $row)
                    <li>
                        <a href="{{ route('tenants.show', ['tenant' => $row['tenant_id'], 'tab' => 'documents', 'subscription' => $row['subscription_id']]) }}" class="text-indigo-600 dark:text-indigo-400">
                            {{ $row['company_name'] }}
                        </a>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('All subscribed tenants have signed contracts on file.') }}</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">{{ __('Integrations by service type') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($integrationSummary as $type => $count)
                    <li class="flex justify-between">
                        <span>{{ $integrationServiceLabels[$type] ?? $type }}</span>
                        <span class="font-semibold tabular-nums">{{ $count }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No tenant integrations configured.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">{{ __('Version registry') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($project->versions as $ver)
                    <li class="flex justify-between"><span class="font-mono">{{ $ver->version }}</span><span class="text-slate-500">{{ $ver->is_current ? __('Current') : $ver->release_type }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No versions registered.') }}</li>
                @endforelse
            </ul>
            <form method="post" action="{{ route('projects.versions.store', $project) }}" class="mt-4 flex flex-wrap gap-2">
                @csrf
                <input name="version" placeholder="v1.0.0" required class="rounded border-slate-300 text-xs dark:border-slate-700 dark:bg-slate-900" />
                <select name="release_type" class="rounded border-slate-300 text-xs dark:border-slate-700 dark:bg-slate-900">
                    <option value="minor">minor</option><option value="major">major</option><option value="patch">patch</option>
                </select>
                <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_current" value="1" /> {{ __('Current') }}</label>
                <button type="submit" class="rounded bg-indigo-600 px-2 py-1 text-xs text-white">{{ __('Add') }}</button>
            </form>
        </div>
    </div>

    @if ($project->technology_stack || $project->notes)
        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            @if ($project->technology_stack)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold">{{ __('Technology stack') }}</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300">{{ $project->technology_stack }}</p>
                </div>
            @endif
            @if ($project->notes)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold">{{ __('Notes') }}</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300">{{ $project->notes }}</p>
                </div>
            @endif
        </div>
    @endif
</x-dashboard-layout>
