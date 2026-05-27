@props(['label', 'value'])

<div>
    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ $label }}</dt>
    <dd class="mt-1 break-all text-sm text-slate-900 dark:text-white">{{ $value ?? '—' }}</dd>
</div>
