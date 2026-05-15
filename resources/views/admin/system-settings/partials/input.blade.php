@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => ''])

<div>
    <x-input-label :for="$name" :value="$label" />
    <x-text-input :id="$name" :name="$name" :type="$type" class="mt-1 block w-full" :value="old($name, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''))" />
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    <x-input-error class="mt-1" :messages="$errors->get($name)" />
</div>
