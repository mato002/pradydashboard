@php
    $meta = old('meta', []);
    $pipeline = [
        ['id' => 'build', 'label' => __('Build')],
        ['id' => 'test', 'label' => __('Test')],
        ['id' => 'deploy', 'label' => __('Deploy')],
        ['id' => 'live', 'label' => __('Live')],
    ];
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Deployment') }}</h3>
    <p class="text-[11px] text-slate-500">{{ __('CI/CD, rollbacks, and release channels') }}</p>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <div class="flex gap-2">
        @foreach ($pipeline as $step)
            <div
                class="infra-pipeline-step"
                :class="{
                    'infra-pipeline-step-active': pipelineStage('{{ $step['id'] }}') === 'active',
                    'infra-pipeline-step-done': pipelineStage('{{ $step['id'] }}') === 'done',
                }"
            >
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-bold"
                    :class="pipelineStage('{{ $step['id'] }}') === 'done' ? 'bg-emerald-500 text-white' : (pipelineStage('{{ $step['id'] }}') === 'active' ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-700')"
                >
                    <span x-show="pipelineStage('{{ $step['id'] }}') === 'done'">✓</span>
                    <span x-show="pipelineStage('{{ $step['id'] }}') !== 'done'">{{ strtoupper(substr($step['id'], 0, 1)) }}</span>
                </span>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">{{ $step['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-dashed border-slate-200/80 bg-slate-50/50 p-4 text-center dark:border-slate-700 dark:bg-slate-950/30">
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Hosted projects') }}</p>
        <p class="mt-1 text-[11px] text-slate-500">{{ __('Link projects after registration from the server detail page.') }}</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('Deployment strategy')" name="meta[deployment_strategy]" type="select" :value="$meta['deployment_strategy'] ?? 'rolling'">
            <option value="rolling">{{ __('Rolling') }}</option>
            <option value="blue_green">{{ __('Blue / green') }}</option>
            <option value="canary">{{ __('Canary') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Release channel')" name="meta[release_channel]" :value="$meta['release_channel'] ?? 'stable'" placeholder="stable / beta" />
        <x-admin.infra-field :label="__('Git integration')" name="meta[git_integration]" :value="$meta['git_integration'] ?? ''" placeholder="github.com/org/repo" />
        <x-admin.infra-field :label="__('CI/CD enabled')" name="meta[ci_cd_enabled]" type="select" :value="$meta['ci_cd_enabled'] ?? '1'">
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Rollback support')" name="meta[rollback_enabled]" type="select" :value="$meta['rollback_enabled'] ?? '1'">
            <option value="1">{{ __('Enabled') }}</option>
            <option value="0">{{ __('Disabled') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Auto backups')" name="meta[auto_backups]" type="select" :value="$meta['auto_backups'] ?? '1'">
            <option value="1">{{ __('Enabled') }}</option>
            <option value="0">{{ __('Disabled') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Monitoring stack')" name="meta[monitoring_stack]" :value="$meta['monitoring_stack'] ?? ''" placeholder="Prometheus, Grafana" />
        <x-admin.infra-field :label="__('Notification channels')" name="meta[notification_channels]" :value="$meta['notification_channels'] ?? ''" placeholder="Slack #infra" />
        <div class="sm:col-span-2">
            <x-admin.infra-field :label="__('Operations notes')" name="notes" type="textarea" :rows="3" :hint="__('Runbooks and escalation paths')" />
        </div>
    </div>
</div>
