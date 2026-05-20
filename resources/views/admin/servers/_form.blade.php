@php
    $meta = old('meta', is_array($server->provisioning_meta ?? null) ? $server->provisioning_meta : []);
    $domainsText = old('hosted_domains_text', isset($server) && $server->exists ? implode("\n", $server->hosted_domains ?? []) : '');
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y';
    $hasToken = $server->exists && $server->hasStoredApiToken();
@endphp

<div class="space-y-5">
    <x-admin.form-section :title="__('Server identity')" :description="__('Name, provider, and network details.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="name" :value="__('Server name')" />
                <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $server->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="provider" :value="__('Provider')" />
                <x-text-input id="provider" name="provider" type="text" :class="$inputClass" :value="old('provider', $server->provider)" placeholder="Hostinger, Hetzner, GCP…" />
                <x-input-error class="mt-2" :messages="$errors->get('provider')" />
            </div>
            <div>
                <x-input-label for="telemetry_mode" :value="__('Telemetry mode')" />
                <select id="telemetry_mode" name="telemetry_mode" class="{{ $selectClass }}">
                    @foreach (['manual' => __('Manual monitoring'), 'basic' => __('Basic checks (reachability + SSL)'), 'whm' => __('WHM live metrics')] as $val => $label)
                        <option value="{{ $val }}" @selected(old('telemetry_mode', $server->telemetry_mode ?? 'basic') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-slate-500">{{ __('WHM mode still requires host + API token.') }}</p>
            </div>
            <div>
                <x-input-label for="ip_address" :value="__('Public IP')" />
                <x-text-input id="ip_address" name="ip_address" type="text" :class="$inputClass.' font-mono'" :value="old('ip_address', $server->ip_address)" />
            </div>
            <div>
                <x-input-label for="meta_hostname" :value="__('Hostname (FQDN)')" />
                <x-text-input id="meta_hostname" name="meta[hostname]" type="text" :class="$inputClass.' font-mono'" :value="old('meta.hostname', $meta['hostname'] ?? '')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="whm_cpanel_reference" :value="__('WHM / cPanel reference URL')" />
                <textarea id="whm_cpanel_reference" name="whm_cpanel_reference" rows="2" class="{{ $textareaClass }}">{{ old('whm_cpanel_reference', $server->whm_cpanel_reference) }}</textarea>
            </div>
            <div>
                <x-input-label for="meta_region" :value="__('Location / region')" />
                <x-text-input id="meta_region" name="meta[region]" type="text" :class="$inputClass" :value="old('meta.region', $meta['region'] ?? '')" />
            </div>
            <div>
                <x-input-label for="meta_operating_system" :value="__('Operating system')" />
                <x-text-input id="meta_operating_system" name="meta[operating_system]" type="text" :class="$inputClass" :value="old('meta.operating_system', $meta['operating_system'] ?? '')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('WHM API (optional)')" :description="__('Encrypted at rest. Leave blank to keep existing token.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="meta_api_endpoint" :value="__('API endpoint')" />
                <x-text-input id="meta_api_endpoint" name="meta[api_endpoint]" type="text" :class="$inputClass.' font-mono'" :value="old('meta.api_endpoint', $meta['api_endpoint'] ?? '')" placeholder="https://hostname:2087" />
            </div>
            <div>
                <x-input-label for="meta_whm_username" :value="__('WHM username')" />
                <x-text-input id="meta_whm_username" name="meta[whm_username]" type="text" :class="$inputClass" :value="old('meta.whm_username', $meta['whm_username'] ?? 'root')" />
            </div>
            <div>
                <x-input-label for="meta_whm_port" :value="__('WHM port')" />
                <x-text-input id="meta_whm_port" name="meta[whm_port]" type="number" :class="$inputClass" :value="old('meta.whm_port', $meta['whm_port'] ?? 2087)" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="meta_api_token" :value="__('API token')" />
                <input
                    id="meta_api_token"
                    name="meta[api_token]"
                    type="password"
                    autocomplete="new-password"
                    placeholder="{{ $hasToken ? \App\Domain\Servers\Support\ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER : '' }}"
                    class="{{ $inputClass }} font-mono"
                    value=""
                />
                @if ($hasToken)
                    <p class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400">{{ __('Token saved. Enter a new value only to replace it.') }}</p>
                @endif
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Capacity & health')" :description="__('Manual values used when telemetry is manual or WHM is unavailable.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="cpu_cores" :value="__('CPU cores')" />
                <x-text-input id="cpu_cores" name="cpu_cores" type="number" min="0" :class="$inputClass" :value="old('cpu_cores', $server->cpu_cores)" />
            </div>
            <div>
                <x-input-label for="ram_gb" :value="__('RAM (GB)')" />
                <x-text-input id="ram_gb" name="ram_gb" type="number" step="0.01" min="0" :class="$inputClass" :value="old('ram_gb', $server->ram_gb)" />
            </div>
            <div>
                <x-input-label for="storage_gb" :value="__('Disk size (GB)')" />
                <x-text-input id="storage_gb" name="storage_gb" type="number" step="0.01" min="0" :class="$inputClass" :value="old('storage_gb', $server->storage_gb)" />
            </div>
            <div>
                <x-input-label for="disk_usage_percent" :value="__('Disk usage %')" />
                <x-text-input id="disk_usage_percent" name="disk_usage_percent" type="number" step="0.01" min="0" max="100" :class="$inputClass" :value="old('disk_usage_percent', $server->disk_usage_percent)" />
            </div>
            <div>
                <x-input-label for="status" :value="__('Server status')" />
                <select id="status" name="status" class="{{ $selectClass }}">
                    @foreach (['online', 'offline', 'warning', 'unknown'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $server->status ?? 'unknown') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="ssl_status" :value="__('SSL status')" />
                <x-text-input id="ssl_status" name="ssl_status" type="text" :class="$inputClass" :value="old('ssl_status', $server->ssl_status)" />
            </div>
            <div>
                <x-input-label for="backup_status" :value="__('Backup status')" />
                <x-text-input id="backup_status" name="backup_status" type="text" :class="$inputClass" :value="old('backup_status', $server->backup_status)" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Financials & domains')" :description="__('Costs, billing, renewal, and hosted domains.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="currency" :value="__('Currency (ISO)')" />
                <x-text-input id="currency" name="currency" type="text" maxlength="3" :class="$inputClass.' uppercase'" :value="old('currency', $server->currency ?? 'KES')" />
            </div>
            <div>
                <x-input-label for="monthly_cost" :value="__('Monthly cost')" />
                <x-text-input id="monthly_cost" name="monthly_cost" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_cost', $server->monthly_cost)" />
            </div>
            <div>
                <x-input-label for="billing_status" :value="__('Billing status')" />
                <x-text-input id="billing_status" name="billing_status" type="text" :class="$inputClass" :value="old('billing_status', $server->billing_status)" placeholder="paid, due, overdue…" />
            </div>
            <div>
                <x-input-label for="renewal_expires_at" :value="__('Renewal / expiry date')" />
                <x-text-input id="renewal_expires_at" name="renewal_expires_at" type="date" :class="$inputClass" :value="old('renewal_expires_at', optional($server->renewal_expires_at)->format('Y-m-d'))" />
            </div>
            <div>
                <x-input-label for="monthly_revenue" :value="__('Monthly revenue (allocated)')" />
                <x-text-input id="monthly_revenue" name="monthly_revenue" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_revenue', $server->monthly_revenue)" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="hosted_domains_text" :value="__('Hosted domains (one per line)')" />
                <textarea id="hosted_domains_text" name="hosted_domains_text" rows="4" class="{{ $textareaClass }} font-mono text-sm">{{ $domainsText }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="notes" :value="__('Access notes')" />
                <textarea id="notes" name="notes" rows="3" class="{{ $textareaClass }}">{{ old('notes', $server->notes) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="meta_access_restrictions" :value="__('Access restrictions')" />
                <textarea id="meta_access_restrictions" name="meta[access_restrictions]" rows="2" class="{{ $textareaClass }}">{{ old('meta.access_restrictions', $meta['access_restrictions'] ?? '') }}</textarea>
            </div>
        </div>
    </x-admin.form-section>
</div>
