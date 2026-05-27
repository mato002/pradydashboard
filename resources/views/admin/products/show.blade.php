@php
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'suspended' => 'warning',
        'archived' => 'neutral',
        default => 'info',
    };
@endphp

<x-dashboard-layout :heading="$product->name" :subheading="__('Product · :slug', ['slug' => $product->slug])">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white shadow-lg shadow-indigo-500/30">
                {{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.status-badge :variant="$statusVariant($product->status)">{{ ucfirst($product->status) }}</x-ui.status-badge>
                    @if ($product->category)
                        <span class="rounded-full bg-slate-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ $product->category }}</span>
                    @endif
                    <span class="font-mono text-xs text-slate-500">{{ $product->slug }}</span>
                </div>
                @if ($product->description)
                    <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-400">{{ $product->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                {{ __('Edit product') }}
            </a>
            <a href="{{ route('hosted-projects.create', ['product_id' => $product->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg">
                {{ __('Add hosted project') }}
            </a>
            <a href="{{ route('tenants.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                {{ __('Add tenant') }}
            </a>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.kpi-card :title="__('Hosted projects')" :value="$meta['hosted_projects_count']" tone="cyan" />
        <x-ui.kpi-card :title="__('Tenants')" :value="$meta['tenants_count']" tone="violet" />
        <x-ui.kpi-card :title="__('Active tenants')" :value="$meta['active_tenants_count']" :sublabel="__(':count suspended', ['count' => $meta['suspended_tenants_count']])" tone="emerald" />
        <x-ui.kpi-card :title="__('Monthly revenue')" :value="'KES '.number_format($meta['monthly_revenue'], 0)" :animate="false" tone="indigo" />
    </div>

    <div class="mb-6 grid gap-5 lg:grid-cols-2">
        <dl class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-5 text-sm shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Product settings') }}</h3>
            <div class="flex justify-between gap-4 border-t border-slate-100 pt-3 dark:border-slate-800">
                <dt class="text-slate-500">{{ __('License API key') }}</dt>
                <dd class="font-mono font-medium">{{ $product->slug }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('Default billing') }}</dt>
                <dd class="font-medium capitalize">{{ str_replace('_', ' ', $product->default_billing_model) }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('License mode') }}</dt>
                <dd class="font-medium capitalize">{{ str_replace('_', ' ', $product->default_license_mode) }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('Created') }}</dt>
                <dd class="font-medium">{{ $product->created_at?->format('M j, Y') }}</dd>
            </div>
        </dl>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Quick actions') }}</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('hosted-projects.index', ['product_id' => $product->id]) }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('View all hosted projects') }} →</a></li>
                <li><a href="{{ route('tenants.index', ['product_id' => $product->id]) }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('View all tenants') }} →</a></li>
                <li><a href="{{ route('license-logs.index') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('License check logs') }} →</a></li>
            </ul>
        </div>
    </div>

    <x-ui.table-panel :title="__('Hosted projects')" :action-href="route('hosted-projects.create', ['product_id' => $product->id])" :action-label="__('Add instance')">
        <table class="prady-table w-full min-w-[640px]">
            <thead>
                <tr>
                    <th>{{ __('Domain') }}</th>
                    <th>{{ __('Environment') }}</th>
                    <th>{{ __('Server') }}</th>
                    <th class="text-right">{{ __('Tenants') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($product->hostedProjects as $hp)
                    <tr class="border-t border-slate-100 dark:border-slate-800">
                        <td class="font-mono text-sm">
                            <a href="{{ route('hosted-projects.show', $hp) }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ $hp->domain }}</a>
                        </td>
                        <td class="capitalize text-sm">{{ $hp->environment }}</td>
                        <td class="text-sm text-slate-600 dark:text-slate-300">{{ $hp->server?->name ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ $hp->tenants_count ?? 0 }}</td>
                        <td><x-ui.status-badge :variant="$statusVariant($hp->status)">{{ $hp->status }}</x-ui.status-badge></td>
                        <td class="text-right">
                            <a href="{{ route('hosted-projects.edit', $hp) }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
                            {{ __('No hosted projects yet.') }}
                            <a href="{{ route('hosted-projects.create', ['product_id' => $product->id]) }}" class="ml-1 font-semibold text-indigo-600">{{ __('Add one') }}</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.table-panel>

    @if ($product->tenants->isNotEmpty())
        <x-ui.table-panel :title="__('Recent tenants')" class="mt-6" :action-href="route('tenants.index', ['product_id' => $product->id])" :action-label="__('View all')">
            <table class="prady-table w-full min-w-[560px]">
                <thead>
                    <tr>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Hosted project') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->tenants as $tenant)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="font-semibold">
                                <a href="{{ route('tenants.show', $tenant) }}" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ $tenant->company_name }}</a>
                            </td>
                            <td class="font-mono text-xs">{{ $tenant->hostedProject?->domain ?? '—' }}</td>
                            <td class="text-sm">{{ $tenant->subscription_plan ?? '—' }}</td>
                            <td><x-ui.status-badge :variant="$statusVariant($tenant->status)">{{ $tenant->status }}</x-ui.status-badge></td>
                            <td class="text-right">
                                <a href="{{ route('tenants.show', $tenant) }}" class="text-xs font-semibold text-slate-500">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.table-panel>
    @endif
</x-dashboard-layout>
