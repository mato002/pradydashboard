@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $eventOptions = ['license.updated', 'tenant.created', 'tenant.suspended', 'payment.received', 'subscription.renewed', 'billing.invoice', 'deployment.completed'];
@endphp

<x-dashboard-layout :heading="__('Add webhook')" :subheading="__('Register an event delivery endpoint')">
    <x-admin.form-shell
        :title="__('Add webhook')"
        :subtitle="__('HTTPS endpoint with HMAC signature verification.')"
        :badge="__('Integrations')"
        :back-href="route('api-credentials.index')"
        :back-label="__('Back to API center')"
    >
        <form method="post" action="{{ route('api-credentials.webhooks.store') }}" class="max-w-4xl space-y-5">
            @csrf
            <x-admin.form-section :title="__('Endpoint')" :description="__('URL and subscribed platform events.')">
                <div class="space-y-5">
                    <div>
                        <x-input-label for="url" :value="__('Endpoint URL')" />
                        <x-text-input id="url" name="url" type="url" :class="$inputClass.' font-mono'" :value="old('url', $webhook['url'])" placeholder="https://api.example.com/webhooks/prady" required />
                        <x-input-error class="mt-2" :messages="$errors->get('url')" />
                    </div>
                    <div>
                        <x-input-label :value="__('Events')" />
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($eventOptions as $event)
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200/80 px-3 py-2 text-sm dark:border-slate-700">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" @checked(in_array($event, old('events', $webhook['events'] ?? []), true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-mono text-xs">{{ $event }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('events')" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="{{ $selectClass }}">
                            @foreach (['active', 'paused', 'degraded'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $webhook['status']) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Register webhook') }}
                </button>
                <a href="{{ route('api-credentials.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
