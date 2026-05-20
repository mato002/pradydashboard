@php
    $contract = $tenantSystemContractDocs ?? [];
@endphp

<div
    x-data="{ open: true, sampleTab: 'json', stubTab: 'routes' }"
    class="mb-6 overflow-hidden rounded-2xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/80 to-white shadow-card dark:border-indigo-900/50 dark:from-indigo-950/40 dark:to-slate-900/60"
>
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
    >
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Tenant System API contract') }}</p>
            <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Every Prady product installation should expose :endpoint for Dashboard outbound checks.', ['endpoint' => $contract['full_endpoint'] ?? 'GET /api/system/info']) }}
            </p>
        </div>
        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400" x-text="open ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
    </button>

    <div x-show="open" x-cloak class="border-t border-indigo-100 px-4 pb-4 pt-3 dark:border-indigo-900/50">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ __('Security') }}</p>
                    <ul class="mt-1 list-inside list-disc text-slate-600 dark:text-slate-400">
                        @foreach ($contract['auth_headers'] ?? [] as $header)
                            <li><code class="text-xs">{{ $header }}</code></li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-slate-500">{{ __('Store the token in the tenant .env as :key. Never return secrets in the JSON body.', ['key' => 'PRADY_DASHBOARD_API_TOKEN']) }}</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ __('Field tiers') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Required (core)') }}: {{ implode(', ', $contract['core_fields'] ?? []) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Recommended') }}: {{ implode(', ', $contract['recommended_fields'] ?? []) }}</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ __('Contract health (after pull)') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <x-ui.status-badge variant="success">{{ __('Valid') }} — {{ __('all core + recommended fields') }}</x-ui.status-badge>
                        <x-ui.status-badge variant="warning">{{ __('Partial') }} — {{ __('core present, some recommended missing') }}</x-ui.status-badge>
                        <x-ui.status-badge variant="danger">{{ __('Invalid') }} — {{ __('missing core fields or unsafe payload') }}</x-ui.status-badge>
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-2 flex gap-1">
                    <button type="button" @click="sampleTab = 'json'" :class="sampleTab === 'json' ? 'bg-white shadow dark:bg-slate-800' : ''" class="rounded-lg px-3 py-1 text-xs font-semibold">{{ __('Sample JSON') }}</button>
                    <button type="button" @click="sampleTab = 'env'" :class="sampleTab === 'env' ? 'bg-white shadow dark:bg-slate-800' : ''" class="rounded-lg px-3 py-1 text-xs font-semibold">{{ __('.env') }}</button>
                </div>
                <pre x-show="sampleTab === 'json'" class="max-h-64 overflow-auto rounded-xl bg-slate-900 p-3 text-xs text-emerald-300">{{ $contract['sample_json'] ?? '{}' }}</pre>
                <pre x-show="sampleTab === 'env'" x-cloak class="max-h-64 overflow-auto rounded-xl bg-slate-900 p-3 text-xs text-sky-300">{{ $contract['stubs']['env'] ?? '' }}</pre>
            </div>
        </div>

        <div class="mt-4">
            <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Laravel sample (copy into tenant product)') }}</p>
            <div class="mb-2 flex flex-wrap gap-1">
                @foreach (['routes' => __('Routes'), 'middleware' => __('Middleware'), 'controller' => __('Controller')] as $key => $label)
                    <button type="button" @click="stubTab = '{{ $key }}'" :class="stubTab === '{{ $key }}' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="rounded-lg px-3 py-1 text-xs font-semibold">{{ $label }}</button>
                @endforeach
            </div>
            @foreach (['routes', 'middleware', 'controller'] as $stubKey)
                <pre x-show="stubTab === '{{ $stubKey }}'" @if ($stubKey !== 'routes') x-cloak @endif class="max-h-72 overflow-auto rounded-xl bg-slate-900 p-3 text-xs text-slate-200">{{ $contract['stubs'][$stubKey] ?? '' }}</pre>
            @endforeach
            <p class="mt-2 text-xs text-slate-500">{{ __('Reference files ship in') }} <code class="text-indigo-600">stubs/tenant-integration/</code></p>
        </div>
    </div>
</div>
