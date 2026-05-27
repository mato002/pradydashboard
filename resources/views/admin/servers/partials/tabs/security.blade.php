@php
    $meta = old('meta', []);
    $toggles = [
        'ssl_enabled' => __('SSL enabled'),
        'auto_renew' => __('Auto renew'),
        'backups_enabled' => __('Backups enabled'),
        'fail2ban' => __('Fail2Ban'),
        'firewall_active' => __('Firewall active'),
        'ssh_key_only' => __('SSH key auth only'),
    ];
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Security') }}</h3>
            <p class="text-[11px] text-slate-500">{{ __('Certificates, perimeter, and compliance posture') }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-emerald-500/15 to-indigo-500/15 px-3 py-2 text-center ring-1 ring-emerald-500/20">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Security score') }}</p>
            <p class="text-xl font-bold tabular-nums text-emerald-700 dark:text-emerald-300"><span x-text="securityScore"></span>%</p>
        </div>
    </div>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($toggles as $key => $label)
            <label
                class="infra-toggle-card"
                :class="securityToggles.{{ $key }} && 'infra-toggle-card-on'"
            >
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                <input type="checkbox" class="sr-only" x-model="securityToggles.{{ $key }}" />
                <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition" :class="securityToggles.{{ $key }} ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600'">
                    <span class="inline-block h-5 w-5 translate-x-0.5 rounded-full bg-white shadow transition" :class="securityToggles.{{ $key }} && 'translate-x-5'"></span>
                </span>
            </label>
        @endforeach
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('SSL status')" name="ssl_status" model="form.ssl_status" placeholder="Valid — Let's Encrypt" />
        <x-admin.infra-field :label="__('Certificate expiry')" name="meta[certificate_expiry]" type="date" :value="$meta['certificate_expiry'] ?? ''" />
        <x-admin.infra-field :label="__('Backup status')" name="backup_status" model="form.backup_status" placeholder="Daily snapshots OK" />
        <x-admin.infra-field :label="__('WAF enabled')" name="meta[waf_enabled]" type="select" :value="$meta['waf_enabled'] ?? ''">
            <option value="">{{ __('Unknown') }}</option>
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Security scan')" name="meta[security_scan_status]" :value="$meta['security_scan_status'] ?? ''" placeholder="0 critical" />
        <x-admin.infra-field :label="__('Monitoring')" name="meta[monitoring_enabled]" type="select" :value="$meta['monitoring_enabled'] ?? '1'">
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
    </div>
</div>
