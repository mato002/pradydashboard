@php
    $tabs = [
        'overview' => __('Overview'),
        'assignments' => __('Assignments'),
        'documents' => __('Documents'),
        'payroll' => __('Payroll reference'),
    ];
@endphp

<x-dashboard-layout :heading="$staff->full_name" :subheading="$staff->staff_number">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2 text-sm">
            <span class="rounded-full bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800">{{ $staff->status }}</span>
            @if ($staff->department)
                <span class="text-gray-500">{{ $staff->department->name }}</span>
            @endif
        </div>
        <a href="{{ route('hr.staff.edit', $staff) }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Edit profile') }}</a>
    </div>

    <nav class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-800">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('hr.staff.show', ['staff' => $staff, 'tab' => $key]) }}"
               class="border-b-2 px-3 py-2 text-sm font-medium {{ $tab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <x-admin.risk-cards :risks="$operationalRisks" class="mb-6" :compact="true" />

    @if ($tab === 'overview')
        <dl class="grid gap-4 sm:grid-cols-2 rounded-xl border border-gray-200 bg-white p-5 text-sm dark:border-gray-800 dark:bg-gray-900">
            <div><dt class="text-gray-500">{{ __('Email') }}</dt><dd class="font-medium">{{ $staff->email ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Phone') }}</dt><dd class="font-medium">{{ $staff->phone ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Job title') }}</dt><dd class="font-medium">{{ $staff->job_title ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Employment') }}</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $staff->employment_type) }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Start date') }}</dt><dd>{{ $staff->start_date?->toFormattedDateString() ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('End date') }}</dt><dd>{{ $staff->end_date?->toFormattedDateString() ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-500">{{ __('Emergency contact') }}</dt><dd>{{ $staff->emergency_contact ?? '—' }}</dd></div>
            @if ($staff->notes)
                <div class="sm:col-span-2"><dt class="text-gray-500">{{ __('Notes') }}</dt><dd class="whitespace-pre-line">{{ $staff->notes }}</dd></div>
            @endif
        </dl>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold">{{ __('Assigned tickets') }}</h3>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($assignedTickets as $ticket)
                        <li class="px-4 py-3 text-sm">
                            <a href="{{ route('support-tickets.show', $ticket->id) }}" class="font-medium text-indigo-600 hover:underline">{{ $ticket->subject }}</a>
                            <p class="text-xs text-gray-500">{{ $ticket->tenant?->company_name }} · {{ $ticket->status }}</p>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No assigned tickets.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold">{{ __('Follow-ups') }}</h3>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($staffFollowUps as $comm)
                        <li class="px-4 py-3 text-sm">
                            <p class="font-medium">{{ \Illuminate\Support\Str::limit($comm->message, 80) }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $comm->tenant?->company_name }}
                                · {{ optional($comm->follow_up_date)->toFormattedDateString() }}
                                @if ($comm->isOverdueFollowUp()) <span class="text-rose-600 font-semibold">{{ __('Overdue') }}</span> @endif
                            </p>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No pending follow-ups.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <x-admin.activity-feed :logs="$activityLogs" class="mt-6" />
    @elseif ($tab === 'assignments')
        @include('admin.hr.staff.partials.assignments')
    @elseif ($tab === 'documents')
        @include('admin.hr.staff.partials.documents')
    @elseif ($tab === 'payroll')
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-5 text-sm dark:border-amber-900 dark:bg-amber-950/20">
            <p class="text-xs font-semibold uppercase text-amber-800">{{ __('Restricted — HR only') }}</p>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div><dt class="text-gray-500">{{ __('Monthly salary') }}</dt><dd class="text-lg font-semibold tabular-nums">{{ $staff->currency }} {{ $staff->monthly_salary !== null ? number_format((float) $staff->monthly_salary, 2) : '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Currency') }}</dt><dd>{{ $staff->currency }}</dd></div>
            </dl>
        </div>
    @endif
</x-dashboard-layout>
