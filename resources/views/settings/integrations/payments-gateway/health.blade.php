<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Gateway Health')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">{{ __('Gateway URL') }}</dt>
                    <dd class="mt-1 break-all font-medium text-slate-900 dark:text-white">{{ $baseUrl }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">{{ __('Admin token configured') }}</dt>
                    <dd class="mt-1">
                        <x-ui.status-badge :variant="$configured ? 'success' : 'danger'">
                            {{ $configured ? __('Yes') : __('No') }}
                        </x-ui.status-badge>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($checks as $check)
                @php
                    $response = $check['response'];
                    $reachable = (bool) ($response['ok'] ?? false);
                    $data = is_array($response['data'] ?? null) ? $response['data'] : [];
                @endphp
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $check['label'] }}</h3>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $check['path'] }}</p>
                        </div>
                        <x-ui.status-badge :variant="$reachable ? 'success' : 'danger'">
                            {{ $reachable ? __('Reachable') : __('Unreachable') }}
                        </x-ui.status-badge>
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('HTTP status') }}</dt>
                            <dd class="tabular-nums">{{ $response['status'] ?? 0 }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Response time') }}</dt>
                            <dd class="tabular-nums">{{ $response['response_time_ms'] ?? 0 }} ms</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Service') }}</dt>
                            <dd>{{ $data['service'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('API status') }}</dt>
                            <dd>{{ $data['status'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Environment') }}</dt>
                            <dd>{{ $data['environment'] ?? config('app.env') }}</dd>
                        </div>
                        @if (! $reachable)
                            <div>
                                <dt class="text-slate-500">{{ __('Error details') }}</dt>
                                <dd class="mt-1 rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-800 dark:bg-rose-950 dark:text-rose-200">
                                    {{ $response['error'] ?? __('Payments Gateway unavailable') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endforeach
        </div>
    </div>
</x-dashboard-layout>
