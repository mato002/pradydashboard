<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Create webhook endpoint')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <x-admin.form-shell
            :title="__('Create webhook endpoint')"
            :subtitle="$profile['name'] ?? $profileUuid"
            :back-href="route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profileUuid)"
            :back-label="__('Back to webhooks')"
            badge="{{ __('Webhook Endpoints') }}"
        >
            <form method="post" action="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.store', $profileUuid) }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Name'), 'name' => 'name', 'required' => true])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('URL'), 'name' => 'url', 'required' => true])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Secret'), 'name' => 'secret', 'hint' => __('Sent to gateway only — masked when displayed')])
                </div>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Events') }}</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($events as $event)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="events[]" value="{{ $event }}" @checked(collect(old('events', []))->contains($event)) class="rounded border-slate-300">
                                {{ $event }}
                            </label>
                        @endforeach
                    </div>
                    @error('events')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profileUuid) }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create endpoint') }}</button>
                </div>
            </form>
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
