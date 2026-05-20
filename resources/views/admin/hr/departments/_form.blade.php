@php $department = $department ?? new \App\Models\HrDepartment; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Name') }}</label>
        <input name="name" value="{{ old('name', $department->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Code') }}</label>
        <input name="code" value="{{ old('code', $department->code) }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div class="sm:col-span-2">
        <label class="text-xs font-medium text-gray-500">{{ __('Description') }}</label>
        <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('description', $department->description) }}</textarea>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Department lead') }}</label>
        <select name="manager_staff_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('None') }}</option>
            @foreach ($managers as $id => $name)
                <option value="{{ $id }}" @selected((string) old('manager_staff_id', $department->manager_staff_id) === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Status') }}</label>
        <select name="status" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $department->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
