@props(['assignments' => collect(), 'title' => __('Assigned staff')])

@if ($assignments->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
        <ul class="mt-3 space-y-2">
            @foreach ($assignments as $assignment)
                <li class="flex flex-wrap items-start justify-between gap-2 text-sm">
                    <div>
                        <a href="{{ route('hr.staff.show', $assignment->staffProfile) }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            {{ $assignment->staffProfile?->full_name }}
                        </a>
                        @if ($assignment->role_on_assignment)
                            <span class="text-gray-500"> — {{ $assignment->role_on_assignment }}</span>
                        @endif
                        @if ($assignment->staffProfile?->department)
                            <p class="text-xs text-gray-500">{{ $assignment->staffProfile->department->name }}</p>
                        @endif
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $assignment->status }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
