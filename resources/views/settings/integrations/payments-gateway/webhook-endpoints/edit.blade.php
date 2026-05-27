<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Edit webhook endpoint')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <x-admin.form-shell
            :title="__('Edit webhook endpoint')"
            :subtitle="$endpoint['name'] ?? $endpointUuid"
            :back-href="route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profileUuid)"
            :back-label="__('Back to webhooks')"
            badge="{{ __('Webhook Endpoints') }}"
        >
            @if ($endpoint)
                <form method="post" action="{{ route('settings.payments-gateway.webhook-endpoints.update', $endpointUuid) }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Name'), 'name' => 'name', 'required' => true, 'value' => $endpoint['name'] ?? ''])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('URL'), 'name' => 'url', 'required' => true, 'value' => $endpoint['url'] ?? ''])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Secret'), 'name' => 'secret', 'placeholder' => __('Leave blank to keep configured secret'), 'hint' => __('Current: :value', ['value' => $endpoint['secret'] ?? __('not set')])])
                    </div>
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Events') }}</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($events as $event)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" @checked(collect(old('events', $endpoint['events'] ?? []))->contains($event)) class="rounded border-slate-300">
                                    {{ $event }}
                                </label>
                            @endforeach
                        </div>
                        @error('events')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-6 flex flex-wrap justify-end gap-2">
                        @if (in_array(strtolower((string) ($endpoint['status'] ?? '')), ['active', 'enabled'], true))
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.webhook-endpoints.disable', $endpointUuid),
                                'label' => __('Disable endpoint'),
                                'confirm' => __('Disable this webhook endpoint?'),
                                'variant' => 'danger',
                            ])
                        @else
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.webhook-endpoints.enable', $endpointUuid),
                                'label' => __('Enable endpoint'),
                                'confirm' => __('Enable this webhook endpoint?'),
                            ])
                        @endif
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Save changes') }}</button>
                    </div>
                </form>
            @endif
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
