<x-dashboard-layout :heading="__('HR & Team')" :subheading="__('Internal staff operations')">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Manage Prady Technologies staff, departments, and assignments.') }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hr.staff.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add staff') }}</a>
            <a href="{{ route('hr.departments.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold">{{ __('Departments') }}</a>
            <a href="{{ route('hr.staff.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold">{{ __('All staff') }}</a>
        </div>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Active staff') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $metrics['active_staff'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Open assignments') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $metrics['open_assignments'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Without assignments') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $metrics['staff_without_assignments'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Exited staff') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $metrics['exited_staff'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Staff by department') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($metrics['by_department'] as $row)
                    <li class="flex justify-between px-4 py-3 text-sm">
                        <span>{{ $row['name'] }}</span>
                        <span class="font-semibold tabular-nums">{{ $row['count'] }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">{{ __('No departments yet.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Upcoming contract expiries') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($metrics['upcoming_contract_expiries'] as $doc)
                    <li class="px-4 py-3 text-sm">
                        <a href="{{ route('hr.staff.show', $doc->staffProfile) }}" class="font-medium text-indigo-600 hover:underline">{{ $doc->staffProfile?->full_name }}</a>
                        <p class="text-xs text-gray-500">{{ $doc->title }} Â· {{ $doc->expiry_date?->toFormattedDateString() }}</p>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">{{ __('No contract expiries in the next 60 days.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($metrics['recent_exits']->isNotEmpty())
        <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Recent exits') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($metrics['recent_exits'] as $staff)
                    <li class="flex justify-between px-4 py-3 text-sm">
                        <a href="{{ route('hr.staff.show', $staff) }}" class="text-indigo-600 hover:underline">{{ $staff->full_name }}</a>
                        <span class="text-gray-500">{{ $staff->end_date?->toFormattedDateString() ?? 'â€”' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-dashboard-layout>
