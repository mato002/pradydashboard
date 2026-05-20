<x-dashboard-layout :heading="__('Staff')" :subheading="__('Team directory')">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('hr.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('← HR overview') }}</a>
        <a href="{{ route('hr.staff.create') }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white">{{ __('Add staff') }}</a>
    </div>
    <form method="get" class="mb-4 flex flex-wrap gap-2">
        <input name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search…') }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
        <select name="department" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('All departments') }}</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept->id }}" @selected((string) $filters['department'] === (string) $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="">{{ __('All statuses') }}</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border px-3 py-1.5 text-sm font-semibold">{{ __('Filter') }}</button>
    </form>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-950">
                <tr>
                    <th class="px-4 py-2">{{ __('Staff') }}</th>
                    <th class="px-4 py-2">{{ __('Department') }}</th>
                    <th class="px-4 py-2">{{ __('Title') }}</th>
                    <th class="px-4 py-2">{{ __('Type') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($staff as $member)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('hr.staff.show', $member) }}" class="font-medium text-indigo-600 hover:underline">{{ $member->full_name }}</a>
                            <p class="font-mono text-xs text-gray-500">{{ $member->staff_number }}</p>
                        </td>
                        <td class="px-4 py-2">{{ $member->department?->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $member->job_title ?? '—' }}</td>
                        <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $member->employment_type) }}</td>
                        <td class="px-4 py-2 capitalize">{{ $member->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No staff records.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
</x-dashboard-layout>
