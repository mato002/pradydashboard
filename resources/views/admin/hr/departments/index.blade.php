<x-dashboard-layout :heading="__('Departments')" :subheading="__('HR departments')">
    <div class="mb-4 flex justify-between">
        <a href="{{ route('hr.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('← HR overview') }}</a>
        <a href="{{ route('hr.departments.create') }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white">{{ __('Add department') }}</a>
    </div>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-950">
                <tr>
                    <th class="px-4 py-2">{{ __('Name') }}</th>
                    <th class="px-4 py-2">{{ __('Code') }}</th>
                    <th class="px-4 py-2">{{ __('Manager') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Active staff') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($departments as $dept)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $dept->name }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $dept->code }}</td>
                        <td class="px-4 py-2">{{ $dept->manager?->full_name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $dept->staff_count }}</td>
                        <td class="px-4 py-2 capitalize">{{ $dept->status }}</td>
                        <td class="px-4 py-2 text-right"><a href="{{ route('hr.departments.edit', $dept) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-layout>
