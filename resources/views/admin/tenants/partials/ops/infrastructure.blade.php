@php
    $subscriptionUrl = fn (int $id) => route('tenants.show', $tenant).'?tab=infrastructure&subscription='.$id;
    $infra = $selectedSubscription?->infrastructure;
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900';
@endphp

@if ($tenant->projectSubscriptions->isEmpty())
    <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
        {{ __('No project subscriptions for this tenant. Add a subscription on the Projects tab first.') }}
    </p>
@else
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Infrastructure allocation for a hosted product subscription.') }}</p>
            @if ($selectedSubscription)
                <p class="mt-1 text-xs text-gray-500">{{ __('Project') }}: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedSubscription->project?->name }}</span></p>
            @endif
        </div>
        <div class="min-w-[14rem]">
            <label for="infra-subscription-picker" class="block text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Project subscription') }}</label>
            <select id="infra-subscription-picker" class="{{ $inputClass }}" onchange="window.location.href = this.value">
                @foreach ($tenant->projectSubscriptions as $sub)
                    <option value="{{ $subscriptionUrl($sub->id) }}" @selected($selectedSubscription?->id === $sub->id)>
                        {{ $sub->project?->name }} — {{ $sub->package_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @unless ($hasRegisteredServers)
        <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
            {{ __('No server registered yet.') }}
            <a href="{{ route('servers.create') }}" class="font-semibold underline">{{ __('Register a server') }}</a>
        </p>
    @endunless

    <form method="post" action="{{ route('tenants.project-subscriptions.infrastructure.update', [$tenant, $selectedSubscription]) }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Assigned server') }}</label>
                <select name="server_id" class="{{ $inputClass }}">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($servers as $srv)
                        <option value="{{ $srv->id }}" @selected(old('server_id', $infra?->server_id) == $srv->id)>{{ $srv->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">{{ __('Links this deployment to a server in the fleet registry.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('cPanel account') }}</label>
                <input type="text" name="cpanel_account" value="{{ old('cpanel_account', $infra?->cpanel_account) }}" class="{{ $inputClass.' font-mono' }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('WHM account reference') }}</label>
                <input type="text" name="whm_account_reference" value="{{ old('whm_account_reference', $infra?->whm_account_reference) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Domain') }}</label>
                <input type="text" name="domain" value="{{ old('domain', $infra?->domain) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Subdomain') }}</label>
                <input type="text" name="subdomain" value="{{ old('subdomain', $infra?->subdomain) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Database name') }}</label>
                <input type="text" name="database_name" value="{{ old('database_name', $infra?->database_name) }}" class="{{ $inputClass.' font-mono' }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Database user') }}</label>
                <input type="text" name="database_user" value="{{ old('database_user', $infra?->database_user) }}" class="{{ $inputClass.' font-mono' }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Disk quota (MB)') }}</label>
                <input type="number" min="0" name="disk_quota_mb" value="{{ old('disk_quota_mb', $infra?->disk_quota_mb) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Disk used (MB)') }}</label>
                <input type="number" min="0" name="disk_used_mb" value="{{ old('disk_used_mb', $infra?->disk_used_mb) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Bandwidth quota (MB)') }}</label>
                <input type="number" min="0" name="bandwidth_quota_mb" value="{{ old('bandwidth_quota_mb', $infra?->bandwidth_quota_mb) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Bandwidth used (MB)') }}</label>
                <input type="number" min="0" name="bandwidth_used_mb" value="{{ old('bandwidth_used_mb', $infra?->bandwidth_used_mb) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('SSL status') }}</label>
                <select name="ssl_status" class="{{ $inputClass }}">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($infraFormOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('ssl_status', $infra?->ssl_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('SSL expiry date') }}</label>
                <input type="date" name="ssl_expiry_date" value="{{ old('ssl_expiry_date', optional($infra?->ssl_expiry_date)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Backup policy') }}</label>
                <select name="backup_policy" class="{{ $inputClass }}">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($backupPolicyOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('backup_policy', $infra?->backup_policy) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Backup status') }}</label>
                <select name="backup_status" class="{{ $inputClass }}">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($backupStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('backup_status', $infra?->backup_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Last backup date') }}</label>
                <input type="date" name="last_backup_at" value="{{ old('last_backup_at', optional($infra?->last_backup_at)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium">{{ __('Deployment path') }}</label>
                <input type="text" name="deployment_path" value="{{ old('deployment_path', $infra?->deployment_path) }}" class="{{ $inputClass.' font-mono' }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Public URL') }}</label>
                <input type="url" name="public_url" value="{{ old('public_url', $infra?->public_url) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Admin URL') }}</label>
                <input type="url" name="admin_url" value="{{ old('admin_url', $infra?->admin_url) }}" class="{{ $inputClass }}" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium">{{ __('Health check URL') }}</label>
                <input type="url" name="health_check_url" value="{{ old('health_check_url', $infra?->health_check_url) }}" class="{{ $inputClass }}" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Reserved for future automated health polling.') }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="{{ $inputClass }}">{{ old('notes', $infra?->notes) }}</textarea>
            </div>
        </div>

        <x-primary-button>{{ __('Save infrastructure') }}</x-primary-button>
    </form>
@endif
