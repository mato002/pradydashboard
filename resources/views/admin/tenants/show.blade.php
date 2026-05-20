@php
    $tabUrl = fn (string $t) => route('tenants.show', $tenant).'?tab='.urlencode($t);
    $tabClass = fn (string $t) => $tab === $t
        ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
@endphp

<x-dashboard-layout :heading="$tenant->company_name">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $tenant->project?->name }} Â· {{ $tenant->project?->domain }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span @class([
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $tenant->status === 'active',
                    'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100' => in_array($tenant->status, ['trial', 'warning', 'overdue'], true),
                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' => in_array($tenant->status, ['suspended', 'restricted', 'terminated'], true),
                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' => $tenant->status === 'cancelled',
                ])>{{ str_replace('_', ' ', $tenant->status) }}</span>
                @if($tenant->alerts->whereNull('dismissed_at')->isNotEmpty())
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ __('Open alerts') }}: {{ $tenant->alerts->whereNull('dismissed_at')->count() }}</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tenants.edit', $tenant).'?return_tab='.urlencode($tab) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Edit tenant') }}</a>
            <form method="post" action="{{ route('tenants.destroy', $tenant) }}" onsubmit="return confirm(@json(__('Delete this tenant?')));">
                @csrf
                @method('delete')
                <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <x-admin.risk-cards :risks="$operationalRisks" class="mb-4" :compact="true" />

    <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
        @foreach ([
            ['label' => __('Active projects'), 'value' => $opsSummary['active_projects']],
            ['label' => __('MRR'), 'value' => $opsSummary['currency'].' '.number_format($opsSummary['monthly_revenue'], 0)],
            ['label' => __('Renewal risk'), 'value' => $opsSummary['renewal_risk']],
            ['label' => __('License issues'), 'value' => $opsSummary['license_issues']],
            ['label' => __('Outdated versions'), 'value' => $opsSummary['outdated_versions']],
            ['label' => __('Open tickets'), 'value' => $opsSummary['open_tickets']],
            ['label' => __('Modules enabled'), 'value' => $opsSummary['modules_enabled']],
            ['label' => __('Modules subscribed'), 'value' => $opsSummary['modules_subscribed']],
            ['label' => __('Module billing'), 'value' => $opsSummary['modules_billing_total'] > 0 ? $opsSummary['modules_currency'].' '.number_format($opsSummary['modules_billing_total'], 2) : 'â€”'],
            ['label' => __('Assigned server'), 'value' => $opsSummary['assigned_server']],
            ['label' => __('SSL status'), 'value' => $opsSummary['ssl_status']],
            ['label' => __('Backup status'), 'value' => $opsSummary['backup_status']],
            ['label' => __('Version status'), 'value' => $opsSummary['version_status']],
            ['label' => __('Update risk'), 'value' => $opsSummary['update_risk']],
            ['label' => __('Documents'), 'value' => $opsSummary['documents_count']],
            ['label' => __('Expiring docs'), 'value' => $opsSummary['documents_expiring']],
            ['label' => __('Missing contracts'), 'value' => $opsSummary['missing_required_contracts']],
            ['label' => __('Active integrations'), 'value' => $opsSummary['integrations_active']],
            ['label' => __('Failing integrations'), 'value' => $opsSummary['integrations_failing']],
            ['label' => __('Integrations untested'), 'value' => $opsSummary['integrations_not_tested']],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-[10px] font-medium uppercase text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-lg font-semibold tabular-nums">{{ $card['value'] }}</p>
            </div>
        @endforeach
        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <p class="text-[10px] font-medium uppercase text-gray-500">{{ __('Infrastructure gaps') }}</p>
            <p class="mt-1 text-sm">{{ $opsSummary['infrastructure_gaps'] > 0 ? __(':n need server', ['n' => $opsSummary['infrastructure_gaps']]) : __('OK') }}</p>
            @if ($opsSummary['public_url'])
                <p class="mt-1 truncate text-xs text-indigo-600 dark:text-indigo-400"><a href="{{ $opsSummary['public_url'] }}" target="_blank" rel="noopener">{{ $opsSummary['public_url'] }}</a></p>
            @endif
        </div>
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Outstanding balance') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $billingKpi['currency'] }} {{ number_format($billingKpi['outstanding'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Plan amount') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $tenant->subscription_amount !== null ? $billingKpi['currency'].' '.number_format((float) $tenant->subscription_amount, 2) : 'â€”' }}</p>
            <p class="mt-1 text-xs text-gray-500 capitalize">{{ $tenant->billing_cycle }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Renewal') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ optional($tenant->renewal_date)->toFormattedDateString() ?? 'â€”' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ __('Grace') }}: {{ $tenant->grace_days }} {{ __('days') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Last payment') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $billingKpi['last_payment'] ? $billingKpi['currency'].' '.number_format((float) $billingKpi['last_payment']->amount, 2) : 'â€”' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ optional($billingKpi['last_payment']?->paid_at)->toFormattedDateString() ?? '' }}</p>
        </div>
    </div>

    <div class="overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex min-w-max gap-1 px-1" aria-label="Tabs">
            @foreach ([
                'overview' => __('Overview'),
                'projects' => __('Projects'),
                'infrastructure' => __('Infrastructure'),
                'billing' => __('Billing'),
                'licensing' => __('Licensing'),
                'modules' => __('Modules'),
                'integrations' => __('Integrations'),
                'versions' => __('Versions'),
                'documents' => __('Documents'),
                'support' => __('Support'),
                'communications' => __('Communications'),
                'notices' => __('Notices'),
                'users' => __('Users'),
                'activity' => __('Activity'),
                'deployments' => __('Deployments'),
                'monitoring' => __('Monitoring'),
                'settings' => __('Settings'),
            ] as $key => $label)
                <a href="{{ $tabUrl($key) }}" @class(['whitespace-nowrap border-b-2 px-3 py-3 text-sm font-medium', $tabClass($key)])>{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <div class="mt-6">
        @if ($tab === 'overview')
            <div class="grid gap-6 lg:grid-cols-3">
                <dl class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Business type') }}</dt><dd class="font-medium text-right">{{ $tenant->business_type ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('KRA PIN') }}</dt><dd class="font-medium">{{ $tenant->kra_pin ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Country') }}</dt><dd class="font-medium">{{ $tenant->country ?? 'â€”' }}</dd></div>
                    <div class="md:col-span-2 flex justify-between gap-4"><dt class="shrink-0 text-gray-500">{{ __('Address') }}</dt><dd class="text-right font-medium">{{ $tenant->physical_address ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Contact') }}</dt><dd class="font-medium text-right">{{ $tenant->contact_person ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Phone / Email') }}</dt><dd class="text-right font-medium">{{ $tenant->phone ?? 'â€”' }} Â· {{ $tenant->email ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Tenant domain') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->tenant_domain ?? 'â€”' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('External key') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->external_key }}</dd></div>
                </dl>
                <div class="space-y-4">
                <dl class="rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold text-gray-900 dark:text-white">{{ __('Deployment snapshot') }}</h3>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Assigned server') }}</dt><dd class="font-medium text-right">{{ $opsSummary['assigned_server'] }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Public URL') }}</dt><dd class="max-w-xs truncate text-right font-medium">@if($opsSummary['public_url'])<a href="{{ $opsSummary['public_url'] }}" class="text-indigo-600" target="_blank">{{ $opsSummary['public_url'] }}</a>@else â€” @endif</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('SSL / Backup') }}</dt><dd class="text-right font-medium">{{ $opsSummary['ssl_status'] }} Â· {{ $opsSummary['backup_status'] }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Version / risk') }}</dt><dd class="text-right font-medium">{{ $opsSummary['version_status'] }} Â· {{ $opsSummary['update_risk'] }}</dd></div>
                </dl>
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-5 text-sm text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                    <h3 class="font-semibold">{{ __('Access & licensing') }}</h3>
                    <p class="mt-2 text-indigo-900/90 dark:text-indigo-200">{{ __('Tenant apps authenticate with the project API token and this external_key, or enterprise POST /api/license/check with tenant_id + domain + product slug.') }}</p>
                    @if($tenant->latestAccessControl)
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
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2">{{ __('Name') }}</th>
                            <th class="px-4 py-2">{{ __('Email') }}</th>
                            <th class="px-4 py-2">{{ __('Last seen') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($tenant->reportedUsers as $u)
                            <tr>
                                <td class="px-4 py-2">{{ $u->name ?? 'â€”' }}</td>
                                <td class="px-4 py-2">{{ $u->email ?? 'â€”' }}</td>
                                <td class="px-4 py-2">{{ optional($u->last_seen_at)->toDayDateTimeString() ?? 'â€”' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ __('No users reported yet. Tenant apps can POST usage to register seats.') }}</td></tr>
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
            <ul class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @forelse ($tenant->project?->deployments ?? [] as $dep)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <span class="font-mono font-medium">{{ $dep->version }}</span>
                        <span class="text-xs text-gray-500">{{ optional($dep->deployed_at)->toDayDateTimeString() ?? 'â€”' }}</span>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No deployment records for this product.') }}</li>
                @endforelse
            </ul>
        @elseif ($tab === 'monitoring')
            @php $m = $tenant->usageMetric; @endphp
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Active users') }}</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $m?->active_users ?? 'â€”' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Database (MB)') }}</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->database_size_mb !== null ? number_format((float) $m->database_size_mb, 1) : 'â€”' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Storage (MB)') }}</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->storage_usage_mb !== null ? number_format((float) $m->storage_usage_mb, 1) : 'â€”' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Server CPU %') }}</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $m?->server_cpu_percent ?? 'â€”' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('App version') }}</p>
                    <p class="mt-2 font-mono text-sm">{{ $m?->reported_app_version ?? $tenant->deployment_version ?? 'â€”' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Last sync') }}</p>
                    <p class="mt-2 text-sm font-medium">{{ optional($m?->last_sync_at)->toDayDateTimeString() ?? 'â€”' }}</p>
                </div>
            </div>
            <div class="mt-6 h-40 rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-4 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-400">
                {{ __('Charts placeholder â€” wire Prometheus / agent metrics in Phase 4.') }}
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Operational notes') }}</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $tenant->notes ?: __('No internal notes.') }}</p>
                <p class="mt-4 text-xs text-gray-500">{{ __('Suspension workflow: reminders â†’ warning banner â†’ restricted transactions â†’ login lockout â†’ restore on payment. Automate via jobs in Phase 3.') }}</p>
            </div>
        @endif
    </div>
</x-dashboard-layout>
