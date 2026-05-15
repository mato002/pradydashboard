@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
@endphp

<x-dashboard-layout :heading="__('Edit role')" :subheading="$role['name']">
    <x-admin.form-shell
        :title="__('Edit role')"
        :subtitle="$slug"
        :badge="__('IAM')"
        :back-href="route('users-roles.roles.show', $slug)"
        :back-label="__('Back to role')"
    >
        <form method="post" action="{{ route('users-roles.roles.update', $slug) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            <x-admin.form-section :title="__('Role definition')">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="name" :value="__('Role name')" />
                        <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $role['name'])" required />
                    </div>
                    <div>
                        <x-input-label for="level" :value="__('Privilege level')" />
                        <x-text-input id="level" name="level" type="number" min="1" max="100" :class="$inputClass" :value="old('level', $role['level'])" required />
                    </div>
                    <div>
                        <x-input-label for="inherits" :value="__('Inherits from')" />
                        <select id="inherits" name="inherits" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($roles as $r)
                                @if ($r['slug'] !== $slug)
                                    <option value="{{ $r['slug'] }}" @selected(old('inherits', $role['inherits']) === $r['slug'])>{{ $r['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $role['description']) }}</textarea>
                    </div>
                </div>
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('users-roles.roles.show', $slug) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
