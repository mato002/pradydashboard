@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
@endphp

<x-dashboard-layout :heading="__('Create role')" :subheading="__('Define a new IAM role')">
    <x-admin.form-shell
        :title="__('Create role')"
        :subtitle="__('Set privilege level, inheritance, and description.')"
        :badge="__('IAM')"
        :back-href="route('users-roles.index')"
        :back-label="__('Back to IAM center')"
    >
        <form method="post" action="{{ route('users-roles.roles.store') }}" class="max-w-4xl space-y-5">
            @csrf
            <x-admin.form-section :title="__('Role definition')">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" :value="__('Role name')" />
                        <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="slug" :value="__('Slug')" />
                        <x-text-input id="slug" name="slug" type="text" :class="$inputClass.' font-mono'" :value="old('slug')" placeholder="tenant_manager" required />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>
                    <div>
                        <x-input-label for="level" :value="__('Privilege level (1–100)')" />
                        <x-text-input id="level" name="level" type="number" min="1" max="100" :class="$inputClass" :value="old('level', 50)" required />
                    </div>
                    <div>
                        <x-input-label for="inherits" :value="__('Inherits from (optional)')" />
                        <select id="inherits" name="inherits" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r['slug'] }}" @selected(old('inherits') === $r['slug'])>{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description') }}</textarea>
                    </div>
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Create role') }}
                </button>
                <a href="{{ route('users-roles.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
