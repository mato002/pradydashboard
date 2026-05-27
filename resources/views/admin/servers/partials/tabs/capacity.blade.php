@php
    $meta = old('meta', []);
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Capacity') }}</h3>
    <p class="text-[11px] text-slate-500">{{ __('Compute profile and utilization signals') }}</p>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <div class="grid gap-3 grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['field' => 'cpu_cores', 'label' => __('CPU cores'), 'unit' => 'cores', 'step' => 1, 'max' => 128, 'name' => 'cpu_cores'],
            ['field' => 'ram_gb', 'label' => __('RAM'), 'unit' => 'GB', 'step' => 1, 'max' => 1024, 'name' => 'ram_gb'],
            ['field' => 'storage_gb', 'label' => __('Storage'), 'unit' => 'GB', 'step' => 10, 'max' => 50000, 'name' => 'storage_gb'],
            ['field' => 'bandwidth_gbps', 'label' => __('Bandwidth'), 'unit' => 'Gbps', 'step' => 0.5, 'max' => 100, 'name' => 'meta[bandwidth_gbps]'],
        ] as $metric)
            <div class="infra-metric-card">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                <div class="mt-2 flex items-center justify-between gap-2">
                    <button type="button" @click="adjustMetric('{{ $metric['field'] }}', -{{ $metric['step'] }}, 0, {{ $metric['max'] }})" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">−</button>
                    <span class="text-lg font-semibold tabular-nums text-slate-900 dark:text-white" x-text="form.{{ $metric['field'] }} || '0'"></span>
                    <button type="button" @click="adjustMetric('{{ $metric['field'] }}', {{ $metric['step'] }}, 0, {{ $metric['max'] }})" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200/80 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">+</button>
                </div>
                <p class="mt-1 text-center text-[10px] text-slate-500">{{ $metric['unit'] }}</p>
                <input type="hidden" name="{{ $metric['name'] }}" x-model="form.{{ $metric['field'] }}" />
            </div>
        @endforeach
        <div class="infra-metric-card col-span-2 lg:col-span-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Network speed') }}</p>
            <input type="text" name="meta[network_speed]" value="{{ $meta['network_speed'] ?? '' }}" placeholder="10 Gbps" class="infra-provision-input mt-2 !min-h-[40px] py-2 text-sm" />
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500">
                <span>{{ __('CPU allocation') }}</span>
                <span class="text-cyan-600" x-text="(form.cpu_cores || 0) + ' cores'"></span>
            </div>
            <div class="infra-provision-meter"><div class="infra-provision-meter-fill" :style="'width:' + Math.min(100, (form.cpu_cores || 0) * 8) + '%'"></div></div>
        </div>
        <div>
            <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500">
                <span>{{ __('RAM pressure') }}</span>
                <span class="text-violet-600" x-text="(form.ram_gb || 0) + ' GB'"></span>
            </div>
            <div class="infra-provision-meter"><div class="infra-provision-meter-fill bg-gradient-to-r from-violet-500 to-fuchsia-500" :style="'width:' + Math.min(100, (form.ram_gb || 0) * 4) + '%'"></div></div>
        </div>
        <div>
            <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500">
                <span>{{ __('Disk utilization') }}</span>
                <span class="text-amber-600" x-text="(form.disk_usage_percent || 0) + '%'"></span>
            </div>
            <div class="infra-provision-meter"><div class="infra-provision-meter-fill bg-gradient-to-r from-amber-500 to-orange-500" :style="'width:' + Math.min(100, form.disk_usage_percent || 0) + '%'"></div></div>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('Disk usage %')" name="disk_usage_percent" type="number" step="0.01" :min="0" :max="100" model="form.disk_usage_percent" />
        <x-admin.infra-field :label="__('Operational status')" name="status" type="select" model="form.status">
            <option value="online">{{ __('Online') }}</option>
            <option value="offline">{{ __('Offline') }}</option>
            <option value="unknown">{{ __('Unknown') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Operating system')" name="meta[operating_system]" :value="$meta['operating_system'] ?? ''" placeholder="Ubuntu 24.04 LTS" />
        <x-admin.infra-field :label="__('Architecture')" name="meta[architecture]" type="select" :value="$meta['architecture'] ?? ''">
            <option value="">{{ __('Select…') }}</option>
            <option value="x86_64" @selected(($meta['architecture'] ?? '') === 'x86_64')>x86_64</option>
            <option value="arm64" @selected(($meta['architecture'] ?? '') === 'arm64')>arm64</option>
        </x-admin.infra-field>
    </div>
</div>
