@php
    $project = $tenant->hostedProject ?? $tenant->project;
    $dashboardUrl = rtrim((string) config('app.url'), '/');
    $productKey = $project?->resolveProductKey() ?? '';
    $envLines = implode("\n", array_filter([
        'PRADY_LICENSE_ENFORCED=true',
        'PRADY_DASHBOARD_URL='.$dashboardUrl,
        'PRADY_PROJECT_API_TOKEN='.($project?->api_token ?? ''),
        'PRADY_TENANT_KEY='.($tenant->tenant_key ?? ''),
        'PRADY_PRODUCT_KEY='.$productKey,
        'PRADY_LICENSE_SECRET='.($tenant->license_secret ?? ''),
        'PRADY_LICENSE_CACHE_TTL=600',
        'PRADY_TENANT_CODE='.($tenant->tenant_code ?? ''),
        'PRADY_PRODUCT_NAME="'.($project?->product?->name ?? $project?->name ?? config('app.name')).'"',
    ]));
@endphp

@if ($project)
    <div class="mb-4 overflow-hidden rounded-xl border border-indigo-200/80 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20">
        <div class="border-b border-indigo-100/80 px-4 py-2 dark:border-indigo-900/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Product .env credentials') }}</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400">{{ __('For :project — copy into the hosted installation', ['project' => $project->name]) }}</p>
        </div>
        <div class="grid gap-2 p-4 sm:grid-cols-2">
            <x-admin.copyable-field :label="__('PRADY_TENANT_KEY')" :value="$tenant->tenant_key ?? ''" />
            <x-admin.copyable-field :label="__('PRADY_TENANT_CODE')" :value="$tenant->tenant_code ?? ''" />
            <x-admin.copyable-field :label="__('PRADY_LICENSE_SECRET')" :value="$tenant->license_secret ?? ''" :masked="true" />
            <x-admin.copyable-field :label="__('tenant_domain')" :value="$tenant->tenant_domain ?? $project->domain" />
            <x-admin.copyable-field :label="__('PRADY_PROJECT_API_TOKEN')" :value="$project->api_token ?? ''" :masked="true" class="sm:col-span-2" />
            <x-admin.copyable-field :label="__('Full .env block')" :value="$envLines" class="sm:col-span-2" />
        </div>
        <p class="border-t border-indigo-100/80 px-4 py-2 text-[11px] text-slate-500 dark:border-indigo-900/50">
            <a href="{{ route('hosted-projects.show', $project) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View all integration fields on hosted project') }} →</a>
        </p>
    </div>
@endif
