@php
    use App\Support\IntegrationServiceOptions;
    use Illuminate\Support\Str;
    $integrationsUrl = fn (int $subscriptionId, ?int $integrationId = null, ?string $section = null) => route('tenants.show', array_filter([
        'tenant' => $tenant,
        'tab' => 'integrations',
        'subscription' => $subscriptionId,
        'integration' => $integrationId,
        'section' => $section,
    ]));
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900';
    $tenantSystemInsights = $tenantSystemApiInsights ?? null;
@endphp

@if ($tenant->projectSubscriptions->isEmpty())
    <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
        {{ __('Subscribe to a project before adding service integrations.') }}
    </p>
@else
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Provider APIs and tenant system endpoints for each product subscription.') }}</p>
        </div>
        <div class="min-w-[14rem]">
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Project subscription') }}</label>
            <select class="{{ $inputClass }}" onchange="window.location.href = this.value">
                @foreach ($tenant->projectSubscriptions as $sub)
                    <option value="{{ $integrationsUrl($sub->id) }}" @selected($selectedSubscription?->id === $sub->id)>
                        {{ $sub->project?->name }} — {{ $sub->package_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($selectedSubscription && $tenantSystemInsights)
        <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                <p class="text-[10px] font-semibold uppercase text-indigo-600 dark:text-indigo-400">{{ __('Tenant system API') }}</p>
                <p class="mt-1 text-sm font-semibold">{{ $tenantSystemInsights['configured'] ? __('Configured') : __('Not configured') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-[10px] font-semibold uppercase text-gray-500">{{ __('Current version') }}</p>
                <p class="mt-1 font-semibold">{{ $tenantSystemInsights['current_version'] ?? '—' }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-[10px] font-semibold uppercase text-gray-500">{{ __('Health') }}</p>
                <p class="mt-1 font-semibold">{{ $tenantSystemInsights['health_status'] }}</p>
                <p class="text-xs text-gray-500">{{ __('Last check') }}: {{ $tenantSystemInsights['last_api_check']?->diffForHumans() ?? __('Never') }}</p>
                @if ($tenantSystemInsights['contract_health_label'] ?? null)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Contract') }}: <span class="font-semibold">{{ $tenantSystemInsights['contract_health_label'] }}</span>
                    </p>
                @endif
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-[10px] font-semibold uppercase text-gray-500">{{ __('Last heartbeat') }}</p>
                <p class="mt-1 font-semibold">{{ $tenantSystemInsights['last_heartbeat']?->diffForHumans() ?? '—' }}</p>
                @if ($tenantSystemInsights['last_error'])
                    <p class="mt-1 text-xs text-rose-600">{{ Str::limit($tenantSystemInsights['last_error'], 80) }}</p>
                @endif
            </div>
        </div>
    @endif

    @if ($selectedSubscription)
        @php
            $allIntegrations = $selectedSubscription->serviceIntegrations;
            $tenantSystemApis = $allIntegrations->filter(fn ($i) => $i->isTenantSystem());
            $providerIntegrations = $allIntegrations->filter(fn ($i) => $i->isProvider());
            $formIntegration = $editingIntegration;
            $formCategory = match (true) {
                request('section') === 'provider' => IntegrationServiceOptions::CATEGORY_PROVIDER,
                request('section') === 'tenant_system', $formIntegration?->isTenantSystem() => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
                default => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            };
            $isTenantSystemForm = $formCategory === IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM;
        @endphp

        <div class="mb-8">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Tenant system APIs') }}</h3>
                <a href="{{ $integrationsUrl($selectedSubscription->id, null, 'tenant_system') }}" class="text-xs font-semibold text-indigo-600">{{ __('+ Add tenant API') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($tenantSystemApis as $integration)
                    <div class="rounded-xl border border-indigo-100 bg-white p-4 text-sm shadow-sm dark:border-indigo-900/50 dark:bg-gray-900">
                        @include('admin.tenants.partials.ops.integration-card', ['integration' => $integration, 'tenantSystem' => true])
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No tenant system API configured.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="mb-8">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Provider integrations') }}</h3>
                <a href="{{ $integrationsUrl($selectedSubscription->id, null, 'provider') }}" class="text-xs font-semibold text-indigo-600">{{ __('+ Add provider') }}</a>
            </div>
            <div class="space-y-3">
                @forelse ($providerIntegrations as $integration)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        @include('admin.tenants.partials.ops.integration-card', ['integration' => $integration, 'tenantSystem' => false])
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No provider integrations for this subscription.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-950">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $formIntegration ? __('Edit integration') : ($isTenantSystemForm ? __('Add tenant system API') : __('Add provider integration')) }}
            </h3>
            <form
                method="post"
                action="{{ $formIntegration
                    ? route('tenants.project-subscriptions.integrations.update', [$tenant, $selectedSubscription, $formIntegration])
                    : route('tenants.project-subscriptions.integrations.store', [$tenant, $selectedSubscription]) }}"
                class="mt-4 grid gap-3 md:grid-cols-2"
            >
                @csrf
                @if ($formIntegration)
                    @method('put')
                @endif

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium">{{ __('Integration type') }}</label>
                    @if ($formIntegration)
                        <input type="hidden" name="integration_category" value="{{ $formIntegration->integration_category }}" />
                        <p class="mt-1 text-sm text-gray-600">{{ $integrationCategories[$formIntegration->integration_category] ?? $formIntegration->integration_category }}</p>
                    @else
                        <input type="hidden" name="integration_category" value="{{ $formCategory }}" />
                        <select class="{{ $inputClass }}" onchange="window.location.href='{{ $integrationsUrl($selectedSubscription->id) }}&section='+(this.value==='tenant_system'?'tenant_system':'provider')">
                            @foreach ($integrationCategories as $value => $label)
                                <option value="{{ $value }}" @selected($formCategory === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                @if ($isTenantSystemForm)
                    <div>
                        <label class="block text-xs font-medium">{{ __('Purpose') }}</label>
                        <select name="purpose" required class="{{ $inputClass }}">
                            @foreach ($tenantSystemPurposes as $value => $label)
                                <option value="{{ $value }}" @selected(old('purpose', $formIntegration?->purpose ?? 'system_info') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium">{{ __('API name') }}</label>
                        <input type="text" name="api_name" value="{{ old('api_name', $formIntegration?->api_name) }}" class="{{ $inputClass }}" placeholder="{{ __('e.g. Mattare system info') }}" />
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-medium">{{ __('Service type') }}</label>
                        <select name="service_type" required class="{{ $inputClass }}">
                            @foreach ($providerServiceTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('service_type', $formIntegration?->service_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium">{{ __('Provider name') }}</label>
                        <input type="text" name="provider_name" value="{{ old('provider_name', $formIntegration?->provider_name) }}" class="{{ $inputClass }}" />
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium">{{ __('Display name') }}</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $formIntegration?->display_name) }}" required class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-xs font-medium">{{ __('Status') }}</label>
                    <select name="status" class="{{ $inputClass }}">
                        @foreach ($integrationStatusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $formIntegration?->status ?? 'not_configured') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium">{{ __('Endpoint URL') }}</label>
                    <input type="url" name="endpoint_url" value="{{ old('endpoint_url', $formIntegration?->endpoint_url) }}" class="{{ $inputClass }}" placeholder="https://tenant.example.com/api/system/info" />
                </div>
                <div>
                    <label class="block text-xs font-medium">{{ __('Authentication') }}</label>
                    <select name="authentication_type" class="{{ $inputClass }}">
                        @foreach ($authenticationTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('authentication_type', $formIntegration?->authentication_type ?? 'none') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium">{{ __('API secret / token') }}</label>
                    <input type="password" name="api_secret" class="{{ $inputClass }}" placeholder="{{ ($formIntegration?->hasStoredSecret()) ? $secretPlaceholder : __('Paste token') }}" autocomplete="new-password" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Leave as :mask to keep the saved secret.', ['mask' => $secretPlaceholder]) }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $formIntegration?->notes) }}</textarea>
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">{{ $formIntegration ? __('Save changes') : __('Save integration') }}</button>
                    @if ($formIntegration)
                        <a href="{{ $integrationsUrl($selectedSubscription->id) }}" class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                    @endif
                </div>
            </form>
        </div>
    @endif
@endif
