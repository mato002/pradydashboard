@php
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'suspended' => 'warning',
        'archived' => 'neutral',
        default => 'info',
    };
@endphp

<x-dashboard-layout :heading="__('Products')" :subheading="__('PradytecAI software systems — MFI, Property, CRM, ISP, SpareMe, and more')">
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Products') }}</p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ __('Product catalog') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('Define your main software systems, then register hosted domains and tenants under each product.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hosted-projects.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    {{ __('Hosted projects') }}
                </a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add product') }}
                </a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.kpi-card :title="__('Products')" :value="$kpis['total_products']" tone="indigo" />
            <x-ui.kpi-card :title="__('Hosted instances')" :value="$kpis['hosted_instances']" tone="cyan" />
            <x-ui.kpi-card :title="__('Tenants')" :value="$kpis['total_tenants']" tone="violet" />
            <x-ui.kpi-card :title="__('Active tenants')" :value="$kpis['active_tenants']" :sublabel="__(':count suspended', ['count' => $kpis['suspended_tenants']])" tone="emerald" />
        </div>

        <x-admin.quick-links group="control_plane" />

        <x-admin.list-toolbar
            :search-value="$filters['q'] ?? ''"
            search-placeholder="{{ __('Search name, slug, category…') }}"
            :export-href="route('products.export', request()->query())"
            :result-count="$products->total()"
        >
            <x-admin.filter-select name="status" :placeholder="__('Status')" :value="$filters['status'] ?? ''" :options="['active' => __('Active'), 'suspended' => __('Suspended'), 'archived' => __('Archived')]" />
            @if ($categoryOptions->isNotEmpty())
                <x-admin.filter-select name="category" :placeholder="__('Category')" :value="$filters['category'] ?? ''" :options="$categoryOptions" />
            @endif
        </x-admin.list-toolbar>

        <x-ui.table-panel :title="__('All products')" :action-href="route('products.create')" :action-label="__('Add product')">
            <table class="prady-table w-full min-w-[900px]">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Slug / API key') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th class="text-right">{{ __('Hosted') }}</th>
                        <th class="text-right">{{ __('Tenants') }}</th>
                        <th class="text-right">{{ __('MRR') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrichedRows as $row)
                        @php
                            $product = $row['product'];
                            $meta = $row['meta'];
                        @endphp
                        <x-ui.clickable-row :href="route('products.show', $product)">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500/20 to-violet-500/20 text-xs font-bold text-indigo-700 dark:text-indigo-200">
                                        {{ mb_strtoupper(mb_substr($product->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $product->name }}</p>
                                        @if ($product->description)
                                            <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($product->description, 60) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $product->slug }}</td>
                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $product->category ? ucfirst($product->category) : '—' }}</td>
                            <td class="text-right tabular-nums font-medium">{{ $meta['hosted_projects_count'] }}</td>
                            <td class="text-right tabular-nums">
                                <span class="font-medium">{{ $meta['tenants_count'] }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $meta['active_tenants_count'] }} {{ __('active') }}</span>
                            </td>
                            <td class="text-right tabular-nums font-medium">KES {{ number_format($meta['monthly_revenue'], 0) }}</td>
                            <td>
                                <x-ui.status-badge :variant="$statusVariant($product->status)">{{ ucfirst($product->status) }}</x-ui.status-badge>
                            </td>
                            <td class="text-right" @click.stop>
                                <x-ui.row-actions-menu>
                                    <x-ui.row-action :href="route('products.show', $product)">{{ __('View') }}</x-ui.row-action>
                                    <x-ui.row-action :href="route('products.edit', $product)">{{ __('Edit') }}</x-ui.row-action>
                                    <x-ui.row-action :href="route('hosted-projects.create', ['product_id' => $product->id])">{{ __('Add hosted project') }}</x-ui.row-action>
                                </x-ui.row-actions-menu>
                            </td>
                        </x-ui.clickable-row>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <p class="text-sm text-slate-500">{{ __('No products yet.') }}</p>
                                <a href="{{ route('products.create') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Create your first product') }} →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <x-slot name="footer">
                <x-admin.pagination-bar :paginator="$products" />
            </x-slot>
        </x-ui.table-panel>
    </div>
</x-dashboard-layout>
