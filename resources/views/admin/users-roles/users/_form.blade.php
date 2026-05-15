@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $isEdit = $user->exists ?? false;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="name" :value="__('Full name')" />
        <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $user->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" :class="$inputClass" :value="old('email', $user->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>
    <div>
        <x-input-label for="password" :value="$isEdit ? __('New password (optional)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" :class="$inputClass" :required="! $isEdit" autocomplete="new-password" />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>
    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" :class="$inputClass" :required="! $isEdit" autocomplete="new-password" />
    </div>
    <div>
        <x-input-label for="department" :value="__('Department')" />
        <select id="department" name="department" class="{{ $selectClass }}">
            <option value="">{{ __('Select department') }}</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept }}" @selected(old('department', $profile['department'] ?? '') === $dept)>{{ $dept }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="primary_role" :value="__('Primary role')" />
        <select id="primary_role" name="primary_role" class="{{ $selectClass }}">
            <option value="">{{ __('Select role') }}</option>
            @foreach ($roles as $role)
                <option value="{{ $role['name'] }}" @selected(old('primary_role', $profile['roles'][0] ?? '') === $role['name'])>{{ $role['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="status" :value="__('Account status')" />
        <select id="status" name="status" class="{{ $selectClass }}" required>
            @foreach (['active', 'invited', 'suspended'] as $s)
                <option value="{{ $s }}" @selected(old('status', $profile['status'] ?? 'active') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
</div>
