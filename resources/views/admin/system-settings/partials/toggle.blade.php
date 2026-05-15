@props(['name', 'label', 'hint' => null, 'checked' => false])

<label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/40">
    <input type="hidden" name="{{ $name }}" value="0" />
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" />
    <span class="min-w-0 flex-1">
        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</span>
        @if ($hint)
            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>
        @endif
    </span>
</label>
