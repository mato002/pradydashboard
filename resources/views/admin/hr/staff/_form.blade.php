<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Full name') }}</label>
        <input name="full_name" value="{{ old('full_name', $staff->full_name) }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Staff number') }}</label>
        <input name="staff_number" value="{{ old('staff_number', $staff->staff_number) }}" placeholder="{{ __('Auto-generated if empty') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Email') }}</label>
        <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Phone') }}</label>
        <input name="phone" value="{{ old('phone', $staff->phone) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Department') }}</label>
        <select name="hr_department_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('None') }}</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept->id }}" @selected((string) old('hr_department_id', $staff->hr_department_id) === (string) $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Job title') }}</label>
        <input name="job_title" value="{{ old('job_title', $staff->job_title) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Employment type') }}</label>
        <select name="employment_type" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            @foreach ($employmentTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_type', $staff->employment_type ?? 'full_time') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Status') }}</label>
        <select name="status" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $staff->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Start date') }}</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($staff->start_date)->toDateString()) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('End date') }}</label>
        <input type="date" name="end_date" value="{{ old('end_date', optional($staff->end_date)->toDateString()) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Linked dashboard user') }}</label>
        <select name="user_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('None') }}</option>
            @foreach ($users as $id => $name)
                <option value="{{ $id }}" @selected((string) old('user_id', $staff->user_id) === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500">{{ __('Emergency contact') }}</label>
        <input name="emergency_contact" value="{{ old('emergency_contact', $staff->emergency_contact) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
    </div>
    <div class="sm:col-span-2 rounded-lg border border-amber-200 bg-amber-50/50 p-3 dark:border-amber-900 dark:bg-amber-950/20">
        <p class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">{{ __('Payroll reference (restricted)') }}</p>
        <div class="mt-2 grid gap-3 sm:grid-cols-2">
            <div>
                <label class="text-xs text-gray-500">{{ __('Monthly salary') }}</label>
                <input type="number" step="0.01" name="monthly_salary" value="{{ old('monthly_salary', $staff->monthly_salary) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs text-gray-500">{{ __('Currency') }}</label>
                <input name="currency" maxlength="3" value="{{ old('currency', $staff->currency ?? 'KES') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
        </div>
    </div>
    <div class="sm:col-span-2">
        <label class="text-xs font-medium text-gray-500">{{ __('Notes') }}</label>
        <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('notes', $staff->notes) }}</textarea>
    </div>
</div>
