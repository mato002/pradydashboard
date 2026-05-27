@php
    $kit = $integrationKit ?? [];
    $primary = $kit['primary_tenant'] ?? null;
@endphp

<div
    class="overflow-hidden rounded-2xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/90 via-white to-cyan-50/50 shadow-card dark:border-indigo-900/50 dark:from-indigo-950/40 dark:via-slate-900 dark:to-cyan-950/20"
    x-data="{ envOpen: true }"
>
    <div class="border-b border-indigo-100/80 px-4 py-3 dark:border-indigo-900/50">
        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Product integration') }}</p>
        <h3 class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ __('MFI / hosted app .env credentials') }}</h3>
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            {{ __('Copy these into the product installation (e.g. :path). Set :domain on each tenant to match the product URL host.', [
                'path' => 'htdocs/mfi/.env',
                'domain' => 'tenant_domain',
            ]) }}
        </p>
    </div>

    <div class="space-y-3 p-4">
        <x-admin.copyable-field :label="__('PRADY_DASHBOARD_URL')" :value="$kit['dashboard_url'] ?? ''" />
        <x-admin.copyable-field :label="__('PRADY_PRODUCT_KEY')" :value="$kit['product_key'] ?? ''" />
        <x-admin.copyable-field :label="__('PRADY_PRODUCT_NAME')" :value="$kit['product_name'] ?? ''" :mono="false" />
        <x-admin.copyable-field :label="__('PRADY_PROJECT_API_TOKEN')" :value="$kit['project_api_token'] ?? ''" :masked="true" />
        <x-admin.copyable-field :label="__('License API endpoint')" :value="$kit['license_endpoint'] ?? ''" />

        @if ($primary)
            <x-admin.copyable-field :label="__('PRADY_TENANT_KEY')" :value="$primary['tenant_key'] ?? ''" />
            <x-admin.copyable-field :label="__('PRADY_TENANT_CODE')" :value="$primary['tenant_code'] ?? ''" />
            <x-admin.copyable-field :label="__('PRADY_LICENSE_SECRET')" :value="$primary['license_secret'] ?? ''" :masked="true" />
            <x-admin.copyable-field :label="__('Tenant domain (must match product host)')" :value="$primary['tenant_domain'] ?? ''" />
        @else
            <div class="rounded-lg border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-semibold">{{ __('No tenant linked yet') }}</p>
                <p class="mt-1 opacity-90">{{ __('Create a tenant for this hosted project to get tenant_key and license_secret.') }}</p>
                <a href="{{ $kit['create_tenant_url'] ?? route('tenants.create') }}" class="mt-2 inline-flex font-semibold text-indigo-700 underline dark:text-indigo-300">{{ __('Add tenant') }} →</a>
            </div>
        @endif

        <x-admin.copyable-field
            :label="__('PRADY_DASHBOARD_API_TOKEN (suggested — paste into product .env)')"
            :value="$kit['suggested_dashboard_api_token'] ?? ''"
            :masked="true"
        />

        @if (count($kit['tenants'] ?? []) > 1)
            <div class="rounded-lg border border-slate-200/80 p-3 dark:border-slate-700">
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Multiple tenants') }}</p>
                <ul class="mt-2 space-y-2 text-xs">
                    @foreach ($kit['tenants'] as $t)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-slate-50 px-2 py-1.5 dark:bg-slate-800/60">
                            <a href="{{ $t['show_url'] }}" class="font-medium text-indigo-600 dark:text-indigo-400">{{ $t['company_name'] }}</a>
                            <code class="font-mono text-[10px] text-slate-500">{{ $t['tenant_key'] }}</code>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg border border-slate-200/80 dark:border-slate-700">
            <button
                type="button"
                @click="envOpen = !envOpen"
                class="flex w-full items-center justify-between px-3 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200"
            >
                {{ __('Copy full .env block') }}
                <span x-text="envOpen ? '−' : '+'"></span>
            </button>
            <div x-show="envOpen" x-cloak class="border-t border-slate-200/80 p-3 dark:border-slate-700">
                <x-admin.copyable-field :label="__('Complete snippet')" :value="$kit['env_block'] ?? ''" :mono="true" />
            </div>
        </div>
    </div>
</div>
