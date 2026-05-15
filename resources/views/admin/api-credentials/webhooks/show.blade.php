@php
    $statusVariant = match ($profile['status'] ?? 'active') {
        'active' => 'success',
        'degraded' => 'warning',
        default => 'danger',
    };
@endphp

<x-dashboard-layout :heading="__('Webhook')" :subheading="$profile['url']">
    <x-admin.form-shell
        :title="__('Webhook endpoint')"
        :subtitle="$profile['url']"
        :badge="__('Integrations')"
        :back-href="route('api-credentials.index')"
        :back-label="__('Back to API center')"
    >
        <x-slot name="actions">
            <a href="{{ route('api-credentials.webhooks.edit', $webhook) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                {{ __('Edit') }}
            </a>
        </x-slot>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <x-admin.form-section :title="__('Subscribed events')">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($profile['events'] ?? [] as $event)
                            <span class="rounded-lg bg-violet-500/10 px-2.5 py-1 font-mono text-xs font-semibold text-violet-700 dark:text-violet-300">{{ $event }}</span>
                        @endforeach
                    </div>
                </x-admin.form-section>

                @if (! empty($profile['timeline']))
                    <x-admin.form-section :title="__('Recent deliveries')">
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($profile['timeline'] as $row)
                                <div class="flex items-center justify-between gap-3 py-2 text-sm">
                                    <span class="font-mono text-xs text-slate-500">{{ $row['time'] }}</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $row['event'] }}</span>
                                    <x-ui.status-badge :variant="$row['status'] === 'success' ? 'success' : ($row['status'] === 'retry' ? 'warning' : 'danger')">{{ $row['code'] }}</x-ui.status-badge>
                                </div>
                            @endforeach
                        </div>
                    </x-admin.form-section>
                @endif
            </div>

            <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Health') }}</h3>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-ui.status-badge :variant="$statusVariant">{{ ucfirst($profile['status']) }}</x-ui.status-badge></dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Delivery rate') }}</dt><dd class="font-medium">{{ $profile['delivery_rate'] }}%</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last delivery') }}</dt><dd class="font-medium">{{ $profile['last_delivery'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Signature') }}</dt><dd class="font-medium">{{ $profile['signature'] ?? 'HMAC-SHA256' }}</dd></div>
                </div>
            </dl>
        </div>
    </x-admin.form-shell>
</x-dashboard-layout>
