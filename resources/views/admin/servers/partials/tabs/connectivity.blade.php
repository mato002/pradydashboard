@php
    $meta = old('meta', []);
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Connectivity') }}</h3>
            <p class="text-[11px] text-slate-500">{{ __('SSH, WHM API, firewall, and DNS') }}</p>
        </div>
        <button type="button" @click="testConnection()" :disabled="testingConnection" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 disabled:opacity-50 dark:text-emerald-300">
            <span x-text="testingConnection ? '{{ __('Testing…') }}' : '{{ __('Test connection') }}'"></span>
        </button>
    </div>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <template x-if="connectionStatus === 'ok'">
        <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-800 dark:text-emerald-200">
            <p class="font-semibold">✓ {{ __('Connection successful') }} <span x-show="probeLatencyMs" x-cloak>· <span x-text="probeLatencyMs"></span>ms</span></p>
            <ul class="mt-1 list-inside list-disc text-[11px]" x-show="probeMessages.length">
                <template x-for="msg in probeMessages" :key="msg"><li x-text="msg"></li></template>
            </ul>
        </div>
    </template>
    <template x-if="connectionStatus === 'fail'">
        <div class="rounded-xl border border-rose-500/25 bg-rose-500/10 px-3 py-2 text-xs text-rose-800 dark:text-rose-200">
            <p class="font-semibold">✗ {{ __('Connection failed') }} <span x-show="probeLatencyMs" x-cloak>· <span x-text="probeLatencyMs"></span>ms</span></p>
            <ul class="mt-1 list-inside list-disc text-[11px]">
                <template x-for="msg in probeMessages" :key="msg"><li x-text="msg"></li></template>
            </ul>
        </div>
    </template>

    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('SSH access') }}</p>
    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('SSH port')" name="meta[ssh_port]" type="number" :value="$meta['ssh_port'] ?? '22'" placeholder="22" />
        <x-admin.infra-field :label="__('SSH username')" name="meta[ssh_username]" :value="$meta['ssh_username'] ?? ''" placeholder="deploy" />
    </div>

    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('WHM API & cPanel') }}</p>
    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('WHM API endpoint')" name="meta[api_endpoint]" :value="$meta['api_endpoint'] ?? ''" placeholder="https://host:2087" class="sm:col-span-2" />
        <x-admin.infra-field :label="__('Cloud instance ID')" name="meta[cloud_instance_id]" :value="$meta['cloud_instance_id'] ?? ''" />
        <x-admin.infra-field :label="__('Auth method')" name="meta[auth_method]" type="select" :value="$meta['auth_method'] ?? ''">
            <option value="">{{ __('Select…') }}</option>
            <option value="ssh_key">{{ __('SSH key') }}</option>
            <option value="api_token">{{ __('API token') }}</option>
        </x-admin.infra-field>
        <div>
            <x-admin.infra-field :label="__('API token')" name="meta[api_token]" :value="$meta['api_token'] ?? ''" :masked="true" />
            <button type="button" @click="showToken = !showToken" class="mt-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400" x-text="showToken ? '{{ __('Hide') }}' : '{{ __('Reveal') }}'"></button>
        </div>
        <x-admin.infra-field :label="__('cPanel integration')" name="meta[cpanel_integration]" :value="$meta['cpanel_integration'] ?? ''" placeholder="Reseller / shared" />
    </div>

    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Perimeter & DNS') }}</p>
    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('Firewall status')" name="meta[firewall_status]" :value="$meta['firewall_status'] ?? ''" placeholder="UFW active" />
        <x-admin.infra-field :label="__('Open ports')" name="meta[open_ports]" :value="$meta['open_ports'] ?? ''" placeholder="22, 80, 443, 2087" />
        <x-admin.infra-field :label="__('DNS resolver')" name="meta[dns_resolver]" :value="$meta['dns_resolver'] ?? ''" placeholder="1.1.1.1, 8.8.8.8" class="sm:col-span-2" />
        <x-admin.infra-field :label="__('Access restrictions')" name="meta[access_restrictions]" type="textarea" :rows="2" :value="$meta['access_restrictions'] ?? ''" class="sm:col-span-2" />
    </div>
</div>
