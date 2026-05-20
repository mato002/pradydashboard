<div class="grid gap-6 lg:grid-cols-2">
    <form method="post" action="{{ route('hr.staff.assignments.store', $staff) }}" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" x-data="{ type: '{{ old('assignable_type', array_key_first($assignableTypes)) }}', options: @js($assignableOptionsMap) }">
        @csrf
        <h3 class="text-sm font-semibold">{{ __('New assignment') }}</h3>
        <div class="mt-3 space-y-3 text-sm">
            <div>
                <label class="text-xs text-gray-500">{{ __('Target type') }}</label>
                <select name="assignable_type" x-model="type" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($assignableTypes as $class => $label)
                        <option value="{{ $class }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">{{ __('Target') }}</label>
                <select name="assignable_id" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    <template x-for="(name, id) in options[type] || {}" :key="id">
                        <option :value="id" x-text="name"></option>
                    </template>
                </select>
            </div>
            <input name="role_on_assignment" placeholder="{{ __('Role (e.g. Project Lead)') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            <textarea name="responsibility_notes" rows="2" placeholder="{{ __('Notes') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <input type="date" name="start_date" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                <input type="date" name="end_date" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($assignmentStatuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white">{{ __('Add assignment') }}</button>
        </div>
    </form>

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold">{{ __('Current assignments') }}</h3>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($staff->assignments as $assignment)
                <li class="px-4 py-3 text-sm">
                    <p class="font-medium">{{ $assignment->assignableTypeLabel() }}: {{ $assignment->assignableLabel() }}</p>
                    @if ($assignment->role_on_assignment)
                        <p class="text-indigo-600">{{ $assignment->role_on_assignment }}</p>
                    @endif
                    <p class="text-xs text-gray-500 capitalize">{{ $assignment->status }} · {{ $assignment->start_date?->toFormattedDateString() ?? __('No start') }}</p>
                    <form method="post" action="{{ route('hr.staff.assignments.destroy', [$staff, $assignment]) }}" class="mt-2" onsubmit="return confirm('{{ __('Remove assignment?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:underline">{{ __('Remove') }}</button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-gray-500">{{ __('No assignments yet.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
