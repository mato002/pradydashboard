@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $eventOptions = ['license.updated', 'tenant.created', 'tenant.suspended', 'payment.received', 'subscription.renewed', 'billing.invoice', 'deployment.completed'];
@endphp

<x-dashboard-layout :heading="__('Edit webhook')" :subheading="$profile['url']">
    <x-admin.form-shell
        :title="__('Edit webhook')"
        :subtitle="$webhook"
        :badge="__('Integrations')"
        :back-href="route('api-credentials.webhooks.show', $webhook)"
        :back-label="__('Back to webhook')"
    >
        <form method="post" action="{{ route('api-credentials.webhooks.update', $webhook) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            <x-admin.form-section :title="__('Endpoint configuration')">
                <div class="space-y-5">
                    <div>
                        <x-input-label for="url" :value="__('Endpoint URL')" />
                        <x-text-input id="url" name="url" type="url" :class="$inputClass.' font-mono'" :value="old('url', $profile['url'])" required />
                    </div>
                    <div>
                        <x-input-label :value="__('Events')" />
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($eventOptions as $event)
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200/80 px-3 py-2 text-sm dark:border-slate-700">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" @checked(in_array($event, old('events', $profile['events'] ?? []), true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-mono text-xs">{{ $event }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="{{ $selectClass }}">
                            @foreach (['active', 'paused', 'degraded', 'failed'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $profile['status']) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('api-credentials.webhooks.show', $webhook) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
