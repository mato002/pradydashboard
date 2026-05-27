<div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="flex min-w-0 items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white shadow-lg shadow-indigo-500/25">
            {{ mb_strtoupper(mb_substr($tenant->company_name, 0, 2)) }}
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm text-slate-500 dark:text-slate-400">
                {{ $tenant->project?->name }}
                @if ($tenant->project?->domain)
                    <span class="text-slate-400">·</span> {{ $tenant->project->domain }}
                @endif
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span @class([
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' => $tenant->status === 'active',
                    'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100' => in_array($tenant->status, ['trial', 'warning', 'overdue'], true),
                    'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200' => in_array($tenant->status, ['suspended', 'restricted', 'terminated'], true),
                    'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' => $tenant->status === 'cancelled',
                ])>{{ str_replace('_', ' ', $tenant->status) }}</span>
                @if ($tenant->alerts->whereNull('dismissed_at')->isNotEmpty())
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200">
                        {{ __('Open alerts') }}: {{ $tenant->alerts->whereNull('dismissed_at')->count() }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="flex shrink-0 flex-wrap gap-2">
        <a
            href="{{ route('tenants.edit', $tenant).'?return_tab='.urlencode($tab) }}"
            data-tenant-full-nav
            class="inline-flex items-center rounded-xl border border-slate-200/80 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >{{ __('Edit tenant') }}</a>
        <form method="post" action="{{ route('tenants.destroy', $tenant) }}" onsubmit="return confirm(@json(__('Delete this tenant?')));">
            @csrf
            @method('delete')
            <button type="submit" class="inline-flex items-center rounded-xl border border-rose-200/80 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">{{ __('Delete') }}</button>
        </form>
    </div>
</div>

@include('admin.tenants.partials.integration-credentials', ['tenant' => $tenant])

<x-admin.risk-cards :risks="$operationalRisks" class="mb-4" :compact="true" />
