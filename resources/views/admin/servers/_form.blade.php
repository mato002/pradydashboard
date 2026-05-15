@php
    $domainsText = old('hosted_domains_text', isset($server) && $server->exists ? implode("\n", $server->hosted_domains ?? []) : '');
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y';
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
                <x-text-input id="provider" name="provider" type="text" :class="$inputClass" :value="old('provider', $server->provider)" placeholder="Hostinger, AWS, …" />
                <x-input-error class="mt-2" :messages="$errors->get('provider')" />
            </div>
            <div>
                <x-input-label for="ip_address" :value="__('IP address')" />
                <x-text-input id="ip_address" name="ip_address" type="text" :class="$inputClass.' font-mono'" :value="old('ip_address', $server->ip_address)" />
                <x-input-error class="mt-2" :messages="$errors->get('ip_address')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="whm_cpanel_reference" :value="__('WHM / cPanel reference')" />
                <textarea id="whm_cpanel_reference" name="whm_cpanel_reference" rows="2" class="{{ $textareaClass }}">{{ old('whm_cpanel_reference', $server->whm_cpanel_reference) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('whm_cpanel_reference')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Capacity & health')" :description="__('Resources, disk usage, and operational status.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="cpu_cores" :value="__('CPU cores')" />
                <x-text-input id="cpu_cores" name="cpu_cores" type="number" min="0" :class="$inputClass" :value="old('cpu_cores', $server->cpu_cores)" />
                <x-input-error class="mt-2" :messages="$errors->get('cpu_cores')" />
            </div>
            <div>
                <x-input-label for="ram_gb" :value="__('RAM (GB)')" />
                <x-text-input id="ram_gb" name="ram_gb" type="number" step="0.01" min="0" :class="$inputClass" :value="old('ram_gb', $server->ram_gb)" />
                <x-input-error class="mt-2" :messages="$errors->get('ram_gb')" />
            </div>
            <div>
                <x-input-label for="storage_gb" :value="__('Storage (GB)')" />
                <x-text-input id="storage_gb" name="storage_gb" type="number" step="0.01" min="0" :class="$inputClass" :value="old('storage_gb', $server->storage_gb)" />
                <x-input-error class="mt-2" :messages="$errors->get('storage_gb')" />
            </div>
            <div>
                <x-input-label for="disk_usage_percent" :value="__('Disk usage %')" />
                <x-text-input id="disk_usage_percent" name="disk_usage_percent" type="number" step="0.01" min="0" max="100" :class="$inputClass" :value="old('disk_usage_percent', $server->disk_usage_percent)" />
                <x-input-error class="mt-2" :messages="$errors->get('disk_usage_percent')" />
            </div>
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="{{ $selectClass }}">
                    @foreach (['online', 'offline', 'unknown'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $server->status ?? 'unknown') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>
            <div>
                <x-input-label for="ssl_status" :value="__('SSL status')" />
                <x-text-input id="ssl_status" name="ssl_status" type="text" :class="$inputClass" :value="old('ssl_status', $server->ssl_status)" />
                <x-input-error class="mt-2" :messages="$errors->get('ssl_status')" />
            </div>
            <div>
                <x-input-label for="backup_status" :value="__('Backup status')" />
                <x-text-input id="backup_status" name="backup_status" type="text" :class="$inputClass" :value="old('backup_status', $server->backup_status)" />
                <x-input-error class="mt-2" :messages="$errors->get('backup_status')" />
            </div>
            <div>
                <x-input-label for="renewal_expires_at" :value="__('Renewal / expiry date')" />
                <x-text-input id="renewal_expires_at" name="renewal_expires_at" type="date" :class="$inputClass" :value="old('renewal_expires_at', optional($server->renewal_expires_at)->format('Y-m-d'))" />
                <x-input-error class="mt-2" :messages="$errors->get('renewal_expires_at')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Financials & domains')" :description="__('Costs, revenue allocation, and hosted domain list.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="currency" :value="__('Currency (ISO)')" />
                <x-text-input id="currency" name="currency" type="text" maxlength="3" :class="$inputClass.' uppercase'" :value="old('currency', $server->currency ?? 'KES')" />
                <x-input-error class="mt-2" :messages="$errors->get('currency')" />
            </div>
            <div>
                <x-input-label for="monthly_cost" :value="__('Monthly cost')" />
                <x-text-input id="monthly_cost" name="monthly_cost" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_cost', $server->monthly_cost)" />
                <x-input-error class="mt-2" :messages="$errors->get('monthly_cost')" />
            </div>
            <div>
                <x-input-label for="monthly_revenue" :value="__('Monthly revenue (allocated)')" />
                <x-text-input id="monthly_revenue" name="monthly_revenue" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_revenue', $server->monthly_revenue)" />
                <x-input-error class="mt-2" :messages="$errors->get('monthly_revenue')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="hosted_domains_text" :value="__('Hosted domains (one per line)')" />
                <textarea id="hosted_domains_text" name="hosted_domains_text" rows="4" class="{{ $textareaClass }} font-mono text-sm">{{ $domainsText }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('hosted_domains_text')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="{{ $textareaClass }}">{{ old('notes', $server->notes) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </div>
    </x-admin.form-section>
</div>
