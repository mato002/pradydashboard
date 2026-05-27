@php
    $meta = old('meta', []);
    $providers = [
        ['label' => 'AWS', 'color' => 'text-amber-600'],
        ['label' => 'DigitalOcean', 'color' => 'text-sky-600'],
        ['label' => 'Hetzner', 'color' => 'text-red-600'],
        ['label' => 'Hostinger', 'color' => 'text-violet-600'],
        ['label' => 'Linode', 'color' => 'text-emerald-600'],
        ['label' => 'Vultr', 'color' => 'text-cyan-600'],
        ['label' => 'cPanel/WHM', 'color' => 'text-orange-600'],
    ];
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Identity') }}</h3>
    <p class="text-[11px] text-slate-500">{{ __('Node naming, provider, and network endpoints') }}</p>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <div class="flex flex-wrap gap-2">
        @foreach ($providers as $p)
            <button
                type="button"
                @click="selectProvider('{{ $p['label'] }}')"
                :class="provider === '{{ $p['label'] }}' ? 'infra-provider-chip-active' : ''"
                class="infra-provider-chip"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-current {{ $p['color'] }}"></span>
                {{ $p['label'] }}
            </button>
        @endforeach
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('Server name')" name="name" :required="true" placeholder="prod-api-01" model="form.name" />
        <div>
            <x-admin.infra-field
                :label="__('Hostname')"
                name="meta[hostname]"
                placeholder="node-01.example.com"
                :value="$meta['hostname'] ?? ''"
                model="form.hostname"
                x-on:input.debounce.400ms="verifyHostname()"
            />
            <p class="mt-1 text-[11px]" x-show="hostnameStatus" x-cloak>
                <span x-show="hostnameStatus === 'valid'" class="text-emerald-600">✓ {{ __('Valid FQDN') }}</span>
                <span x-show="hostnameStatus === 'invalid'" class="text-rose-600">✗ {{ __('Invalid hostname') }}</span>
            </p>
        </div>
        <input type="hidden" name="provider" x-model="form.provider" />
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Environment') }}</label>
            <select name="meta[environment]" x-model="environment" class="infra-provision-select mt-1.5">
                <option value="production">{{ __('Production') }}</option>
                <option value="staging">{{ __('Staging') }}</option>
                <option value="development">{{ __('Development') }}</option>
            </select>
        </div>
        <x-admin.infra-field :label="__('Region / zone')" name="meta[region]" placeholder="af-south-1" :value="$meta['region'] ?? ''" model="form.region" />
        <x-admin.infra-field :label="__('Public IP')" name="ip_address" placeholder="203.0.113.10" model="form.ip_address" />
        <x-admin.infra-field :label="__('Private IP')" name="meta[private_ip]" placeholder="10.0.1.42" :value="$meta['private_ip'] ?? ''" model="form.private_ip" />
        <x-admin.infra-field :label="__('WHM URL')" name="meta[api_endpoint]" placeholder="https://host:2087" :value="$meta['api_endpoint'] ?? ''" class="sm:col-span-2" :hint="__('Panel API base URL — also used for Validate WHM')" />
        <div class="sm:col-span-2">
            <x-admin.infra-field :label="__('WHM / cPanel reference')" name="whm_cpanel_reference" type="textarea" :rows="2" :hint="__('Account ID, reseller tag, or notes')" />
        </div>
    </div>
</div>
