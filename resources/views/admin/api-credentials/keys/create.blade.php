@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
@endphp

<x-dashboard-layout :heading="__('Generate API key')" :subheading="__('Provision credentials for a hosted product')">
    <x-admin.form-shell
        :title="__('Generate API key')"
        :subtitle="__('Creates a license API token for the selected project.')"
        :badge="__('Developer platform')"
        :back-href="route('api-credentials.index')"
        :back-label="__('Back to API center')"
    >
        <form method="post" action="{{ route('api-credentials.keys.store') }}" class="max-w-4xl space-y-5">
            @csrf
            <x-admin.form-section :title="__('Key configuration')" :description="__('Project scope, label, and lifecycle state.')">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="project_id" :value="__('Project')" />
                        <select id="project_id" name="project_id" class="{{ $selectClass }}" required>
                            <option value="">{{ __('Select project') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="name" :value="__('Key label')" />
                        <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $key['name'])" placeholder="{{ __('Production License API') }}" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="{{ $selectClass }}" required>
                            @foreach (['active', 'suspended', 'revoked'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $key['status']) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Generate key') }}
                </button>
                <a href="{{ route('api-credentials.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
