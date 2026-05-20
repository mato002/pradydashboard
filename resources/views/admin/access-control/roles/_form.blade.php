@php $role = $role ?? null; @endphp
<div>
    <label class="text-xs font-semibold text-slate-600">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $role?->name) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
</div>
<div>
    <label class="text-xs font-semibold text-slate-600">{{ __('Code') }}</label>
    <input name="code" value="{{ old('code', $role?->code) }}" @disabled($role?->is_system) required pattern="[a-z0-9_]+" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950">
</div>
<div>
    <label class="text-xs font-semibold text-slate-600">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('description', $role?->description) }}</textarea>
</div>
<div>
    <label class="text-xs font-semibold text-slate-600">{{ __('Status') }}</label>
    <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        @foreach (['active', 'inactive'] as $status)
            <option value="{{ $status }}" @selected(old('status', $role?->status?->value ?? 'active') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</div>
<div class="flex items-center gap-2">
    <input type="hidden" name="requires_elevation" value="0">
    <input type="checkbox" name="requires_elevation" value="1" id="requires_elevation" @checked(old('requires_elevation', $role?->requires_elevation))>
    <label for="requires_elevation" class="text-sm">{{ __('Requires elevation (password) before activation') }}</label>
</div>
<div>
    <label class="text-xs font-semibold text-slate-600">{{ __('Notes') }}</label>
    <textarea name="notes" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('notes', $role?->notes) }}</textarea>
</div>
