@php
    $initialTab = request('tab', 'providers');
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'failing' => 'danger',
        'suspended' => 'warning',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('API & Integrations')" :subheading="__('Provider APIs, tenant system endpoints, project keys, and communication analytics')">
    <div
        x-data="{ activeTab: @js($initialTab) }"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Integration hub') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('API & Integrations') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('Monitor APIs Prady consumes from providers and APIs exposed by deployed tenant systems back to this dashboard.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('api-credentials.keys.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Generate project API key') }}
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ([
                ['key' => 'total_configured', 'title' => __('APIs configured')],
                ['key' => 'active_apis', 'title' => __('Active APIs')],
                ['key' => 'failing_apis', 'title' => __('Failing APIs')],
                ['key' => 'requests_today', 'title' => __('Checks today')],
                ['key' => 'avg_response', 'title' => __('Avg response time')],
                ['key' => 'project_keys', 'title' => __('Project API keys')],
            ] as $card)
                <x-ui.kpi-card
                    :title="$card['title']"
                    :value="(string) $kpis[$card['key']]['value']"
                    :sublabel="$kpis[$card['key']]['sublabel']"
                    :tone="$kpis[$card['key']]['tone']"
                    :animate="false"
                />
            @endforeach
        </div>

        <div class="flex flex-wrap gap-1 rounded-xl border border-slate-200/80 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-900/80">
            @foreach ([
                'providers' => __('Provider Integrations'),
                'tenant_system' => __('Tenant System APIs'),
                'project_keys' => __('Project API Keys'),
                'webhooks' => __('Webhooks'),
                'analytics' => __('API Logs / Analytics'),
            ] as $tab => $label)
                <button
                    type="button"
                    @click="activeTab = '{{ $tab }}'"
                    :class="activeTab === '{{ $tab }}' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                    class="rounded-lg px-4 py-2 text-xs font-semibold transition"
                >{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="activeTab === 'providers'" x-cloak>
            @include('admin.api-credentials.partials.tab-providers', ['integrations' => $providerIntegrations])
        </div>

        <div x-show="activeTab === 'tenant_system'" x-cloak>
            @include('admin.api-credentials.partials.tenant-system-contract')
            @include('admin.api-credentials.partials.tab-tenant-system', ['apis' => $tenantSystemApis])
        </div>

        <div x-show="activeTab === 'project_keys'" x-cloak>
            @include('admin.api-credentials.partials.tab-project-keys', ['keys' => $projectApiKeys])
        </div>

        <div x-show="activeTab === 'webhooks'" x-cloak>
            @include('admin.api-credentials.partials.tab-webhooks')
        </div>

        <div x-show="activeTab === 'analytics'" x-cloak>
            @include('admin.api-credentials.partials.tab-analytics', ['summary' => $summary, 'recentChecks' => $recentChecks])
        </div>
    </div>
</x-dashboard-layout>
