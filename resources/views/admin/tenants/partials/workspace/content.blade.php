<div
    id="tenant-workspace-panel"
    class="tenant-workspace-panel relative min-h-[12rem] transition-opacity duration-200"
    data-tenant-tab="{{ $tab }}"
    role="region"
    aria-label="{{ $workspaceTabs[$tab] ?? __('Workspace') }}"
>
    @if ($tab === 'overview')
        <div class="grid gap-6 lg:grid-cols-3">
            <dl class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Business type') }}</dt><dd class="text-right font-medium">{{ $tenant->business_type ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('KRA PIN') }}</dt><dd class="font-medium">{{ $tenant->kra_pin ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Country') }}</dt><dd class="font-medium">{{ $tenant->country ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="shrink-0 text-slate-500">{{ __('Address') }}</dt><dd class="text-right font-medium">{{ $tenant->physical_address ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Contact') }}</dt><dd class="text-right font-medium">{{ $tenant->contact_person ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Phone / Email') }}</dt><dd class="text-right font-medium">{{ $tenant->phone ?? '—' }} · {{ $tenant->email ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Tenant domain') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->tenant_domain ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('External key') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->external_key }}</dd></div>
            </dl>
            <div class="space-y-4">
                <dl class="rounded-xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-3 font-semibold text-slate-900 dark:text-white">{{ __('Deployment snapshot') }}</h3>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Assigned server') }}</dt><dd class="text-right font-medium">{{ $opsSummary['assigned_server'] }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Public URL') }}</dt><dd class="max-w-xs truncate text-right font-medium">@if($opsSummary['public_url'])<a href="{{ $opsSummary['public_url'] }}" class="text-indigo-600 dark:text-indigo-400" target="_blank" rel="noopener">{{ $opsSummary['public_url'] }}</a>@else — @endif</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('SSL / Backup') }}</dt><dd class="text-right font-medium">{{ $opsSummary['ssl_status'] }} · {{ $opsSummary['backup_status'] }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Version / risk') }}</dt><dd class="text-right font-medium">{{ $opsSummary['version_status'] }} · {{ $opsSummary['update_risk'] }}</dd></div>
                </dl>
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-5 text-sm text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                    <h3 class="font-semibold">{{ __('Access & licensing') }}</h3>
                    <p class="mt-2 text-indigo-900/90 dark:text-indigo-200">{{ __('Tenant apps authenticate with the project API token and this external_key, or enterprise POST /api/license/check with tenant_id + domain + product slug.') }}</p>
                    @if ($tenant->latestAccessControl)
                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-indigo-800 dark:text-indigo-300">{{ __('Latest control') }}: {{ $tenant->latestAccessControl->level }}</p>
                    @endif
                </div>
            </div>
        </div>
        <x-admin.assigned-staff :assignments="$staffAssignments" :title="__('Account team')" class="mt-6" />
    @elseif ($tab === 'billing')
        @include('admin.tenants.partials.ops.billing')
    @elseif ($tab === 'projects')
        @include('admin.tenants.partials.ops.projects')
    @elseif ($tab === 'licensing')
        @include('admin.tenants.partials.ops.licensing')
    @elseif ($tab === 'integrations')
        @include('admin.tenants.partials.ops.integrations')
    @elseif ($tab === 'versions')
        @include('admin.tenants.partials.ops.versions')
    @elseif ($tab === 'documents')
        @include('admin.tenants.partials.ops.documents')
    @elseif ($tab === 'communications')
        @include('admin.tenants.partials.ops.communications')
    @elseif ($tab === 'infrastructure')
        @include('admin.tenants.partials.ops.infrastructure')
    @elseif ($tab === 'modules')
        @include('admin.tenants.partials.ops.modules')
    @elseif ($tab === 'users')
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-2">{{ __('Name') }}</th>
                        <th class="px-4 py-2">{{ __('Email') }}</th>
                        <th class="px-4 py-2">{{ __('Last seen') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($tenant->reportedUsers as $u)
                        <tr>
                            <td class="px-4 py-2">{{ $u->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $u->email ?? '—' }}</td>
                            <td class="px-4 py-2">{{ optional($u->last_seen_at)->toDayDateTimeString() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">{{ __('No users reported yet. Tenant apps can POST usage to register seats.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif ($tab === 'activity')
        <x-admin.activity-feed :logs="$systemActivityLogs" class="mt-0" />
    @elseif ($tab === 'support')
        @include('admin.tenants.partials.ops.support')
    @elseif ($tab === 'notices')
        @include('admin.tenants.partials.ops.notices')
    @elseif ($tab === 'deployments')
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200/80 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            @forelse ($tenant->project?->deployments ?? [] as $dep)
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span class="font-mono font-medium">{{ $dep->version }}</span>
                    <span class="text-xs text-slate-500">{{ optional($dep->deployed_at)->toDayDateTimeString() ?? '—' }}</span>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No deployment records for this product.') }}</li>
            @endforelse
        </ul>
    @elseif ($tab === 'monitoring')
        @php $m = $tenant->usageMetric; @endphp
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('Active users') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ $m?->active_users ?? '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('Database (MB)') }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->database_size_mb !== null ? number_format((float) $m->database_size_mb, 1) : '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('Storage (MB)') }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->storage_usage_mb !== null ? number_format((float) $m->storage_usage_mb, 1) : '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('Server CPU %') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ $m?->server_cpu_percent ?? '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('App version') }}</p>
                <p class="mt-2 font-mono text-sm">{{ $m?->reported_app_version ?? $tenant->deployment_version ?? '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500">{{ __('Last sync') }}</p>
                <p class="mt-2 text-sm font-medium">{{ optional($m?->last_sync_at)->toDayDateTimeString() ?? '—' }}</p>
            </div>
        </div>
        <div class="mt-6 h-40 rounded-xl border border-dashed border-slate-300 bg-slate-50/80 p-4 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-400">
            {{ __('Charts placeholder — wire Prometheus / agent metrics in Phase 4.') }}
        </div>
    @else
        <div class="rounded-xl border border-slate-200/80 bg-white p-6 text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Operational notes') }}</h3>
            <p class="mt-2 whitespace-pre-wrap">{{ $tenant->notes ?: __('No internal notes.') }}</p>
            <p class="mt-4 text-xs text-slate-500">{{ __('Suspension workflow: reminders → warning banner → restricted transactions → login lockout → restore on payment. Automate via jobs in Phase 3.') }}</p>
        </div>
    @endif
</div>
