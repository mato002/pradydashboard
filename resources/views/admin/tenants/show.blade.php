@php
    $tabUrl = fn (string $t) => route('tenants.show', $tenant).'?tab='.urlencode($t);
    $tabClass = fn (string $t) => $tab === $t
        ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
@endphp

<x-dashboard-layout :heading="$tenant->company_name">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $tenant->project?->name }} · {{ $tenant->project?->domain }}</p>
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

    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Outstanding balance') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $billingKpi['currency'] }} {{ number_format($billingKpi['outstanding'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Plan amount') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $tenant->subscription_amount !== null ? $billingKpi['currency'].' '.number_format((float) $tenant->subscription_amount, 2) : '—' }}</p>
            <p class="mt-1 text-xs text-gray-500 capitalize">{{ $tenant->billing_cycle }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Renewal') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ optional($tenant->renewal_date)->toFormattedDateString() ?? '—' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ __('Grace') }}: {{ $tenant->grace_days }} {{ __('days') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Last payment') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $billingKpi['last_payment'] ? $billingKpi['currency'].' '.number_format((float) $billingKpi['last_payment']->amount, 2) : '—' }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ optional($billingKpi['last_payment']?->paid_at)->toFormattedDateString() ?? '' }}</p>
        </div>
    </div>

    <div class="overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex min-w-max gap-1 px-1" aria-label="Tabs">
            @foreach ([
                'overview' => __('Overview'),
                'billing' => __('Billing'),
                'infrastructure' => __('Infrastructure'),
                'modules' => __('Modules'),
                'users' => __('Users'),
                'activity' => __('Activity'),
                'support' => __('Support'),
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
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Business type') }}</dt><dd class="font-medium text-right">{{ $tenant->business_type ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('KRA PIN') }}</dt><dd class="font-medium">{{ $tenant->kra_pin ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Country') }}</dt><dd class="font-medium">{{ $tenant->country ?? '—' }}</dd></div>
                    <div class="md:col-span-2 flex justify-between gap-4"><dt class="shrink-0 text-gray-500">{{ __('Address') }}</dt><dd class="text-right font-medium">{{ $tenant->physical_address ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Contact') }}</dt><dd class="font-medium text-right">{{ $tenant->contact_person ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Phone / Email') }}</dt><dd class="text-right font-medium">{{ $tenant->phone ?? '—' }} · {{ $tenant->email ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Tenant domain') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->tenant_domain ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('External key') }}</dt><dd class="max-w-xs truncate font-mono text-xs">{{ $tenant->external_key }}</dd></div>
                </dl>
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-5 text-sm text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                    <h3 class="font-semibold">{{ __('Access & licensing') }}</h3>
                    <p class="mt-2 text-indigo-900/90 dark:text-indigo-200">{{ __('Tenant apps authenticate with the project API token and this external_key, or enterprise POST /api/license/check with tenant_id + domain + product slug.') }}</p>
                    @if($tenant->latestAccessControl)
                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-indigo-800 dark:text-indigo-300">{{ __('Latest control') }}: {{ $tenant->latestAccessControl->level }}</p>
                    @endif
                </div>
            </div>
        @elseif ($tab === 'billing')
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Subscriptions') }}</h3>
                    </div>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($tenant->subscriptions as $sub)
                            <li class="px-4 py-3 text-sm">{{ $sub->plan_name }} — {{ $sub->status }} · {{ $billingKpi['currency'] }} {{ number_format((float) $sub->amount, 2) }}</li>
                        @empty
                            <li class="px-4 py-6 text-sm text-gray-500">{{ __('No subscription rows yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Recent invoices') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-2">{{ __('Invoice') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Due') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Penalty') }}</th>
                                    <th class="px-4 py-2">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($tenant->invoices as $inv)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $inv->invoice_number }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $inv->amount_due, 2) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $inv->penalty_amount, 2) }}</td>
                                        <td class="px-4 py-2 capitalize">{{ $inv->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Payments') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Amount') }}</th>
                                <th class="px-4 py-2">{{ __('Method') }}</th>
                                <th class="px-4 py-2">{{ __('Reference') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($tenant->payments as $pay)
                                <tr>
                                    <td class="px-4 py-2">{{ optional($pay->paid_at)->toFormattedDateString() ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $pay->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ $pay->method ?? '—' }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $pay->reference ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">{{ __('No payments recorded.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ __('Penalties (rolled up)') }}: {{ $billingKpi['currency'] }} {{ number_format((float) $tenant->penalties_total, 2) }}</p>
        @elseif ($tab === 'infrastructure')
            <dl class="grid gap-4 rounded-xl border border-gray-200 bg-white p-6 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 md:grid-cols-2">
                <div><dt class="text-gray-500">{{ __('Server') }}</dt><dd class="mt-1 font-medium">{{ $tenant->server?->name ?? $tenant->project?->server?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('cPanel user') }}</dt><dd class="mt-1 font-mono text-xs">{{ $tenant->cpanel_account_ref ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Database') }}</dt><dd class="mt-1 font-mono text-xs">{{ $tenant->database_ref ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Deployment version') }}</dt><dd class="mt-1 font-mono text-xs">{{ $tenant->deployment_version ?? '—' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-gray-500">{{ __('Application URL') }}</dt><dd class="mt-1 break-all font-medium text-indigo-600 dark:text-indigo-400">@if($tenant->login_url)<a href="{{ $tenant->login_url }}" target="_blank" rel="noopener">{{ $tenant->login_url }}</a>@else — @endif</dd></div>
            </dl>
        @elseif ($tab === 'modules')
            <form method="post" action="{{ route('tenants.modules.update', $tenant) }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                @csrf
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Toggle modules licensed for this tenant. Restrictions from access controls and billing may still hide modules at runtime.') }}</p>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($moduleCatalog as $mod)
                        @php
                            $pivot = $tenant->licenseModules->firstWhere('id', $mod->id)?->pivot;
                            $enabled = $pivot ? (bool) $pivot->enabled : true;
                        @endphp
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                            <span>
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $mod->label }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $mod->key }}</span>
                            </span>
                            <input type="hidden" name="modules[{{ $mod->id }}][id]" value="{{ $mod->id }}" />
                            <input type="hidden" name="modules[{{ $mod->id }}][enabled]" value="0" />
                            <input type="checkbox" name="modules[{{ $mod->id }}][enabled]" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900" @checked($enabled) />
                        </label>
                    @endforeach
                </div>
                <x-primary-button>{{ __('Save modules') }}</x-primary-button>
            </form>
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
                                <td class="px-4 py-2">{{ $u->name ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $u->email ?? '—' }}</td>
                                <td class="px-4 py-2">{{ optional($u->last_seen_at)->toDayDateTimeString() ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ __('No users reported yet. Tenant apps can POST usage to register seats.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif ($tab === 'activity')
            <ul class="space-y-3">
                @forelse ($tenant->activityLogs as $log)
                    <li class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $log->action }}</span>
                            <time class="text-xs text-gray-500" datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->diffForHumans() }}</time>
                        </div>
                        @if($log->summary)
                            <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $log->summary }}</p>
                        @endif
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-600">{{ __('No activity recorded yet.') }}</li>
                @endforelse
            </ul>
        @elseif ($tab === 'support')
            <ul class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @forelse ($tenant->supportTickets as $ticket)
                    <li class="px-4 py-3 text-sm">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</span>
                        <span class="ms-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $ticket->status }}</span>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No tickets linked.') }}</li>
                @endforelse
            </ul>
        @elseif ($tab === 'deployments')
            <ul class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @forelse ($tenant->project?->deployments ?? [] as $dep)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <span class="font-mono font-medium">{{ $dep->version }}</span>
                        <span class="text-xs text-gray-500">{{ optional($dep->deployed_at)->toDayDateTimeString() ?? '—' }}</span>
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
                    <p class="mt-2 text-2xl font-semibold">{{ $m?->active_users ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Database (MB)') }}</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->database_size_mb !== null ? number_format((float) $m->database_size_mb, 1) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Storage (MB)') }}</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $m?->storage_usage_mb !== null ? number_format((float) $m->storage_usage_mb, 1) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Server CPU %') }}</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $m?->server_cpu_percent ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('App version') }}</p>
                    <p class="mt-2 font-mono text-sm">{{ $m?->reported_app_version ?? $tenant->deployment_version ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase text-gray-500">{{ __('Last sync') }}</p>
                    <p class="mt-2 text-sm font-medium">{{ optional($m?->last_sync_at)->toDayDateTimeString() ?? '—' }}</p>
                </div>
            </div>
            <div class="mt-6 h-40 rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-4 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-400">
                {{ __('Charts placeholder — wire Prometheus / agent metrics in Phase 4.') }}
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Operational notes') }}</h3>
                <p class="mt-2 whitespace-pre-wrap">{{ $tenant->notes ?: __('No internal notes.') }}</p>
                <p class="mt-4 text-xs text-gray-500">{{ __('Suspension workflow: reminders → warning banner → restricted transactions → login lockout → restore on payment. Automate via jobs in Phase 3.') }}</p>
            </div>
        @endif
    </div>
</x-dashboard-layout>
