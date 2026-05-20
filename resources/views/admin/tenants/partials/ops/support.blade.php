@php
    $tabUrl = fn (string $t, ?int $ticketId = null) => route('tenants.show', array_filter([
        'tenant' => $tenant,
        'tab' => $t,
        'ticket' => $ticketId,
    ]));
@endphp

<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Open tickets') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $supportOps['open_tickets']->count() }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Urgent') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-rose-600">{{ $supportOps['urgent_tickets']->count() }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Overdue') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600">{{ $supportOps['overdue_tickets']->count() }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Pending follow-ups') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $supportOps['pending_follow_ups']->count() }}</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1 space-y-4">
        <form method="post" action="{{ route('tenants.support-tickets.store', $tenant) }}" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            @csrf
            <h3 class="text-sm font-semibold">{{ __('New ticket') }}</h3>
            <div class="mt-3 space-y-2 text-sm">
                <input name="subject" required placeholder="{{ __('Subject') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                <textarea name="description" rows="3" placeholder="{{ __('Description') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
                <select name="category" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($supportCategories as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                </select>
                <select name="priority" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($supportPriorities as $v => $l) <option value="{{ $v }}" @selected($v === 'medium')>{{ $l }}</option> @endforeach
                </select>
                <select name="source" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($supportSources as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                </select>
                <select name="assigned_staff_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($staffList as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                </select>
                <input type="datetime-local" name="due_at" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
                <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white">{{ __('Create ticket') }}</button>
            </div>
        </form>

        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Tickets') }}</h3>
            </div>
            <ul class="max-h-96 divide-y divide-gray-200 overflow-y-auto dark:divide-gray-800">
                @forelse ($supportOps['recent_tickets'] as $t)
                    <li>
                        <a href="{{ $tabUrl('support', $t->id) }}" @class(['block px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-950', $selectedTicket?->id === $t->id ? 'bg-indigo-50 dark:bg-indigo-950/30' : ''])>
                            <p class="font-medium">{{ $t->subject }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ $t->status }} · {{ $t->priority }}</p>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No support tickets.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="lg:col-span-2">
        @if ($selectedTicket)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h3 class="font-semibold">{{ $selectedTicket->subject }}</h3>
                            <p class="text-xs text-gray-500 capitalize">{{ $selectedTicket->category }} · {{ $selectedTicket->priority }} · {{ $selectedTicket->status }}</p>
                        </div>
                        <a href="{{ route('support-tickets.show', $selectedTicket->id) }}" class="text-xs text-indigo-600 hover:underline">{{ __('Full view') }}</a>
                    </div>
                </div>
                @if ($selectedTicket->description)
                    <p class="border-b border-gray-200 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">{{ $selectedTicket->description }}</p>
                @endif

                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h4 class="text-xs font-semibold uppercase text-gray-500">{{ __('Timeline') }}</h4>
                    <ul class="mt-3 space-y-3">
                        @forelse ($selectedTicket->comments as $comment)
                            <li class="rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                                <p class="text-xs text-gray-500">{{ $comment->authorName() }} · {{ $comment->created_at->diffForHumans() }} · {{ $commentTypes[$comment->comment_type] ?? $comment->comment_type }}</p>
                                <p class="mt-1">{{ $comment->message }}</p>
                                <span class="mt-1 inline-block text-[10px] uppercase text-gray-400">{{ $visibilities[$comment->visibility] ?? $comment->visibility }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">{{ __('No comments yet.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="grid gap-4 p-4 lg:grid-cols-2">
                    <form method="post" action="{{ route('tenants.support-tickets.comments.store', [$tenant, $selectedTicket]) }}" class="text-sm">
                        @csrf
                        <p class="font-semibold">{{ __('Add comment') }}</p>
                        <textarea name="message" rows="2" required class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
                        <select name="comment_type" class="mt-2 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($commentTypes as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                        </select>
                        <select name="visibility" class="mt-2 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($visibilities as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                        </select>
                        <button type="submit" class="mt-2 rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Post') }}</button>
                    </form>

                    @if (! in_array($selectedTicket->status, ['resolved', 'closed']))
                        <form method="post" action="{{ route('tenants.support-tickets.resolve', [$tenant, $selectedTicket]) }}" class="text-sm">
                            @csrf
                            <p class="font-semibold">{{ __('Resolve') }}</p>
                            <textarea name="resolution_notes" rows="2" required class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
                            <button type="submit" class="mt-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Mark resolved') }}</button>
                        </form>
                    @endif
                </div>

                <form method="post" action="{{ route('tenants.support-tickets.update', [$tenant, $selectedTicket]) }}" class="border-t border-gray-200 p-4 dark:border-gray-800">
                    @csrf
                    @method('PUT')
                    <p class="mb-2 text-xs font-semibold uppercase text-gray-500">{{ __('Update ticket') }}</p>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($supportStatuses as $v => $l)
                                <option value="{{ $v }}" @selected($selectedTicket->status === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <select name="assigned_staff_id" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($staffList as $id => $name)
                                <option value="{{ $id }}" @selected($selectedTicket->assigned_staff_id == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        @else
            <p class="rounded-xl border border-dashed border-gray-300 p-12 text-center text-sm text-gray-500 dark:border-gray-700">
                {{ __('Select a ticket or create a new one.') }}
            </p>
        @endif
    </div>
</div>
