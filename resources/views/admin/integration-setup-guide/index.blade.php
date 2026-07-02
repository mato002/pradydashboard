@php
    $initialSection = $initialSection ?? 'overview';
    $validSections = array_merge(['overview', 'product_app', 'api_reference', 'troubleshooting'], array_column($guide['checklist'], 'key'));
    if (! in_array($initialSection, $validSections, true)) {
        $initialSection = 'overview';
    }
@endphp

<x-dashboard-layout
    :heading="__('Integration Setup Guide')"
    :subheading="__('Step-by-step API documentation for connecting your product systems to this dashboard')"
>
    <div
        x-data="{ section: @js($initialSection), stubTab: 'license_middleware' }"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Control plane setup') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Integration Setup Guide') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Wire hosted product apps (Property, MFI, CRM, client domains), payments gateway, and CI/CD to this dashboard. All communication is server-to-server HTTP — no shared sessions.') }}
                </p>
            </div>
            @if (count($adminLinks) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($adminLinks as $link)
                        <a href="{{ $link['url'] }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            {{ $link['label'] }} →
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid gap-4 lg:grid-cols-4">
            <nav class="space-y-1 lg:col-span-1">
                <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ __('Setup steps') }}</p>
                @foreach ([
                    'overview' => __('Overview & checklist'),
                    'product_app' => __('Product app (Cursor)'),
                    'dashboard' => __('1. Dashboard'),
                    'product' => __('2. Hosted product'),
                    'tenant' => __('3. Tenant'),
                    'license' => __('4. License API'),
                    'system_info' => __('5. System info API'),
                    'heartbeat' => __('6. Usage heartbeat'),
                    'payments' => __('7. Payments Gateway'),
                    'deployments' => __('8. CI webhooks'),
                    'verify' => __('9. Verify'),
                    'api_reference' => __('API reference'),
                    'troubleshooting' => __('Troubleshooting'),
                ] as $key => $label)
                    <button
                        type="button"
                        @click="section = '{{ $key }}'"
                        :class="section === '{{ $key }}' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold transition"
                    >{{ $label }}</button>
                @endforeach
            </nav>

            <div class="lg:col-span-3 space-y-6">
                {{-- Overview --}}
                <div x-show="section === 'overview'" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('End-to-end checklist') }}</h3>
                        <ol class="mt-4 space-y-3">
                            @foreach ($guide['checklist'] as $item)
                                <li class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">{{ $item['step'] }}</span>
                                    <div>
                                        <button type="button" @click="section = '{{ $item['key'] }}'" class="text-left text-sm font-semibold text-indigo-700 hover:underline dark:text-indigo-400">{{ $item['label'] }}</button>
                                        <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">{{ $item['description'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <x-admin.copyable-field :label="__('Dashboard base URL')" :value="$guide['app_url']" />
                    <x-admin.copyable-field :label="__('API base URL')" :value="$guide['api_base']" />
                    <div class="rounded-2xl border border-violet-200/80 bg-violet-50/50 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                        <p class="text-sm font-semibold text-violet-900 dark:text-violet-200">{{ __('Implementing in another repo (MFI, Property, CRM)?') }}</p>
                        <p class="mt-1 text-xs text-violet-800/90 dark:text-violet-300/90">{{ __('Open the Product app (Cursor) section for the implementation brief, stub file list, and copy-paste agent prompt.') }}</p>
                        <button type="button" @click="section = 'product_app'" class="mt-2 text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Go to Product app guide') }} →</button>
                    </div>
                </div>

                {{-- Product app implementation (for Cursor / other repos) --}}
                <div x-show="section === 'product_app'" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-violet-200/80 bg-gradient-to-r from-violet-50/80 to-white p-4 dark:border-violet-900/50 dark:from-violet-950/30 dark:to-slate-900">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Product app implementation brief') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Use this when wiring a hosted product in Cursor or another IDE. Complete dashboard steps (tenant + credentials) first.') }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-admin.copyable-field label="Brief (in repo)" :value="$guide['product_implementation']['brief_doc']" :mono="false" />
                        <x-admin.copyable-field label="Stubs directory" :value="$guide['product_implementation']['stubs_dir']" :mono="false" />
                        <x-admin.copyable-field label="Cursor rule (copy to product)" :value="$guide['product_implementation']['cursor_rule']" :mono="false" />
                        <x-admin.copyable-field label="Acceptance checks" :value="$guide['product_implementation']['acceptance_checks']" :mono="false" />
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Stub file</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Required</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($guide['product_implementation']['stub_files'] as $stub)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $stub['file'] }}</td>
                                        <td class="px-4 py-2 text-xs">{{ $stub['required'] ? 'Yes' : 'Optional' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Cursor agent prompt (paste in product project)') }}</p>
                        <x-admin.copyable-field label="Prompt" :value="$guide['product_implementation']['cursor_prompt']" :mono="true" />
                    </div>
                </div>

                {{-- Dashboard setup --}}
                <div x-show="section === 'dashboard'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['dashboard']])
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">{{ __('Variable') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">{{ __('Purpose') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($guide['env_dashboard'] as $row)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs text-indigo-700 dark:text-indigo-400">{{ $row['key'] }}</td>
                                        <td class="px-4 py-2 text-slate-600 dark:text-slate-400">{{ $row['purpose'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">{{ __('Run') }} <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">php artisan migrate</code> {{ __('and start a queue worker') }} (<code class="rounded bg-slate-100 px-1 dark:bg-slate-800">php artisan queue:work</code> {{ __('or Horizon).') }}</p>
                </div>

                {{-- Hosted product --}}
                <div x-show="section === 'product'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['product']])
                    <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li>{{ __('Go to Infrastructure → Hosted Projects and create a project.') }}</li>
                        <li>{{ __('Set domain and product_key (e.g. property, mfi, crm).') }}</li>
                        <li>{{ __('Copy the auto-generated API token — this becomes PRADY_PROJECT_API_TOKEN in each product installation.') }}</li>
                    </ul>
                    @permission('projects.view')
                        <a href="{{ route('hosted-projects.index') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Hosted Projects') }} →</a>
                    @endpermission
                </div>

                {{-- Tenant --}}
                <div x-show="section === 'tenant'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['tenant']])
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($guide['tenant_fields'] as $field => $desc)
                            <div class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-700">
                                <code class="text-xs font-semibold text-indigo-700 dark:text-indigo-400">{{ $field }}</code>
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                    @permission('tenants.view')
                        <a href="{{ route('tenants.index') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Tenants') }} →</a>
                    @endpermission
                </div>

                {{-- License API --}}
                <div x-show="section === 'license'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['license']])
                    <div class="mt-4 space-y-3">
                        <x-admin.copyable-field label="API endpoint" :value="$guide['api_base'].'/v1/license/check'" />
                        <x-admin.copyable-field :label="__('Product .env block')" :value="$guide['env_product_license']" :mono="true" />
                    </div>
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Sample request body') }}</p>
                        <pre class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-300">{
  "tenant_key": "abc-properties",
  "product_key": "property",
  "domain": "abc.property.pradytecai.com"
}</pre>
                    </div>
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Laravel stubs (copy to product app)') }}</p>
                        <div class="mb-2 flex flex-wrap gap-1">
                            @foreach (['license_middleware' => __('Middleware'), 'license_config' => __('Config'), 'license_routes' => __('Routes')] as $key => $label)
                                <button type="button" @click="stubTab = '{{ $key }}'" :class="stubTab === '{{ $key }}' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" class="rounded-lg px-3 py-1 text-xs font-semibold">{{ $label }}</button>
                            @endforeach
                        </div>
                        @foreach (['license_middleware', 'license_config', 'license_routes'] as $stubKey)
                            <pre x-show="stubTab === '{{ $stubKey }}'" @if ($stubKey !== 'license_middleware') x-cloak @endif class="max-h-80 overflow-auto rounded-xl bg-slate-900 p-3 text-xs text-slate-200">{{ $guide['stubs'][$stubKey] }}</pre>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                        <p class="font-semibold">{{ __('Access levels') }}</p>
                        <p class="mt-1">full → normal · warning → banner · read_only → block mutations · blocked → deny access</p>
                    </div>
                </div>

                {{-- System info --}}
                <div x-show="section === 'system_info'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['system_info']])
                    <div class="mt-4 space-y-3">
                        <x-admin.copyable-field :label="__('Endpoint (on each product app)')" value="GET /api/system/info" />
                        <x-admin.copyable-field :label="__('Auth header')" value="Authorization: Bearer {PRADY_DASHBOARD_API_TOKEN}" />
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('See sample JSON, .env snippet, and Laravel stubs under Settings → API & Integrations → Tenant System APIs.') }}</p>
                        @permission('api_credentials.view')
                            <a href="{{ route('api-credentials.index', ['tab' => 'tenant_system']) }}" class="inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Tenant System API contract') }} →</a>
                        @endpermission
                    </div>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ __('After exposing the endpoint, add a Tenant system API integration on the tenant Integrations tab with the same token as PRADY_DASHBOARD_API_TOKEN.') }}</p>
                </div>

                {{-- Heartbeat --}}
                <div x-show="section === 'heartbeat'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['heartbeat']])
                    <x-admin.copyable-field label="API endpoint" :value="$guide['api_base'].'/v1/tenant/usage'" />
                    <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-300">{
  "tenant_key": "&lt;tenant external_key UUID&gt;",
  "active_users": 18,
  "database_size_mb": 420.5,
  "storage_usage_mb": 1200,
  "reported_app_version": "2.4.1"
}</pre>
                    <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">{{ __('Note: tenant_key in this API is the external_key UUID, not the human-readable tenant_key used in license checks.') }}</p>
                </div>

                {{-- Payments --}}
                <div x-show="section === 'payments'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['payments']])
                    <div class="mt-4 space-y-3">
                        <x-admin.copyable-field :label="__('Inbound webhook URL (configure on gateway)')" :value="$guide['api_base'].'/v1/payments-gateway/webhooks'" />
                        <x-admin.copyable-field :label="__('PAYMENTS_GATEWAY_URL')" :value="config('payment_gateway.base_url')" />
                    </div>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ __('Link tenants via Settings → Payments Gateway → Treasury Mapping. Tenant payment listener:') }} <code class="text-xs">https://{domain}/webhooks/payments-gateway/events</code></p>
                    @permission('payments_gateway.view')
                        <a href="{{ route('settings.payments-gateway.overview') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Payments Gateway') }} →</a>
                    @endpermission
                </div>

                {{-- Deployments --}}
                <div x-show="section === 'deployments'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['deployments']])
                    <x-admin.copyable-field :label="__('Webhook URL pattern')" :value="$guide['api_base'].'/v1/deployments/webhooks/{integration_id}'" />
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ __('Create a Deployment Integration in the dashboard, then configure your CI provider with Bearer token or X-Hub-Signature-256 (GitHub style).') }}</p>
                    @permission('deployments.view')
                        <a href="{{ route('deployments.index') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Deployments') }} →</a>
                    @endpermission
                </div>

                {{-- Verify --}}
                <div x-show="section === 'verify'" x-cloak>
                    @include('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['verify']])
                    <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li>{{ __('Tenant command center → Integration readiness checklist') }}</li>
                        <li>{{ __('Integrations tab → Test connection / Pull system info') }}</li>
                        <li>{{ __('License Logs — confirm successful checks from product apps') }}</li>
                        <li>{{ __('curl license check from product server with real credentials') }}</li>
                        <li><code class="text-xs">php artisan ops:health --json</code> {{ __('on dashboard server') }}</li>
                    </ul>
                </div>

                {{-- API reference --}}
                <div x-show="section === 'api_reference'" x-cloak>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('API reference') }}</h3>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">HTTP method</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">URL path</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Authentication</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Traffic direction</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($guide['endpoints'] as $ep)
                                    <tr>
                                        <td class="px-3 py-2"><span class="rounded bg-indigo-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">{{ $ep['method'] }}</span></td>
                                        <td class="px-3 py-2 font-mono text-xs break-all">{{ $ep['path'] }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-400">{{ $ep['auth'] }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-400">{{ $ep['direction'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Troubleshooting --}}
                <div x-show="section === 'troubleshooting'" x-cloak>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Common issues') }}</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($guide['troubleshooting'] as $row)
                            <div class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-700">
                                <p class="text-sm font-semibold text-rose-700 dark:text-rose-400">{{ $row['symptom'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Cause') }}: {{ $row['cause'] }}</p>
                                <p class="mt-1 text-xs text-slate-700 dark:text-slate-300">{{ __('Fix') }}: {{ $row['fix'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
