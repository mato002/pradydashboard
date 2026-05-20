<div class="mb-6 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="text-sm font-semibold">{{ __('Support — open tickets') }}</h3>
        <p class="text-xs text-gray-500">{{ __('Tenants with unresolved issues: :count', ['count' => $supportSummary['tenants_with_issues']]) }}</p>
    </div>
    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
        @forelse ($supportSummary['open_tickets'] as $ticket)
            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                <div>
                    <a href="{{ route('support-tickets.show', $ticket->id) }}" class="font-medium text-indigo-600 hover:underline">{{ $ticket->subject }}</a>
                    <p class="text-xs text-gray-500 capitalize">{{ $ticket->priority }} · {{ $ticket->status }}</p>
                </div>
                @if ($ticket->tenant)
                    <a href="{{ route('tenants.show', ['tenant' => $ticket->tenant_id, 'tab' => 'support', 'ticket' => $ticket->id]) }}" class="text-xs text-gray-500 hover:text-indigo-600">{{ $ticket->tenant->company_name }}</a>
                @endif
            </li>
        @empty
            <li class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No open tickets for this project.') }}</li>
        @endforelse
    </ul>
</div>
