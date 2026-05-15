@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
@endphp

<x-dashboard-layout :heading="__('Edit API key')" :subheading="$profile['name']">
    <x-admin.form-shell
        :title="__('Edit API key')"
        :subtitle="$profile['masked_token']"
        :badge="__('Developer platform')"
        :back-href="route('api-credentials.keys.show', $key)"
        :back-label="__('Back to key')"
    >
        @if ($isDemo)
            <div class="mb-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('Demo keys are read-only. Generate a key linked to a project to persist changes.') }}
            </div>
        @endif
        <form method="post" action="{{ route('api-credentials.keys.update', $key) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            <x-admin.form-section :title="__('Key settings')">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="name" :value="__('Key label')" />
                        <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $profile['name'])" required />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="{{ $selectClass }}" required>
                            @foreach (['active', 'suspended', 'revoked'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $profile['status']) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless ($isDemo)
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="checkbox" name="regenerate" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Rotate token on save') }}
                            </label>
                        </div>
                    @endunless
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('api-credentials.keys.show', $key) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
