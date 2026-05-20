<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Recent') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums">{{ $supportOps['recent_communications']->count() }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Pending follow-ups') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600">{{ $supportOps['pending_follow_ups']->count() }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Overdue follow-ups') }}</p>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-rose-600">
            {{ $supportOps['pending_follow_ups']->filter(fn ($c) => $c->isOverdueFollowUp())->count() }}
        </p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <form method="post" action="{{ route('tenants.communications.store', $tenant) }}" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
        @csrf
        <h3 class="text-sm font-semibold">{{ __('Log communication') }}</h3>
        <div class="mt-3 space-y-2 text-sm">
            <select name="channel" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($commChannels as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
            </select>
            <select name="direction" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($commDirections as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
            </select>
            <input name="subject" placeholder="{{ __('Subject (optional)') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            <textarea name="message" rows="3" required placeholder="{{ __('Summary') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
            <input type="datetime-local" name="communication_date" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <select name="staff_profile_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="">{{ __('Staff (optional)') }}</option>
                @foreach ($staffList as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
            </select>
            <select name="related_support_ticket_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="">{{ __('Related ticket (optional)') }}</option>
                @foreach ($tenant->supportTickets as $ticket)
                    <option value="{{ $ticket->id }}">#{{ $ticket->id }} — {{ $ticket->subject }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-xs">
                <input type="checkbox" name="follow_up_required" value="1" class="rounded border-gray-300" />
                {{ __('Follow-up required') }}
            </label>
            <input type="date" name="follow_up_date" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white">{{ __('Log') }}</button>
        </div>
    </form>

    <div class="lg:col-span-2 space-y-4">
        @if ($supportOps['pending_follow_ups']->isNotEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20">
                <div class="border-b border-amber-200 px-4 py-3 dark:border-amber-900">
                    <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('Pending follow-ups') }}</h3>
                </div>
                <ul class="divide-y divide-amber-100 dark:divide-amber-900/50">
                    @foreach ($supportOps['pending_follow_ups'] as $comm)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium">{{ $commChannels[$comm->channel] ?? $comm->channel }} — {{ \Illuminate\Support\Str::limit($comm->message, 80) }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($comm->follow_up_date)->toFormattedDateString() ?? __('No date') }}
                                    @if ($comm->isOverdueFollowUp())
                                        <span class="text-rose-600 font-semibold">{{ __('Overdue') }}</span>
                                    @endif
                                </p>
                            </div>
                            <form method="post" action="{{ route('tenants.communications.status', [$tenant, $comm]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed" />
                                <button type="submit" class="rounded border px-2 py-1 text-xs font-semibold">{{ __('Complete') }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Communication history') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($supportOps['recent_communications'] as $comm)
                    <li class="px-4 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium capitalize">{{ $commChannels[$comm->channel] ?? $comm->channel }} · {{ $commDirections[$comm->direction] ?? $comm->direction }}</span>
                            <span class="text-xs text-gray-500">{{ $comm->communication_date->format('M j, Y g:i A') }}</span>
                        </div>
                        @if ($comm->subject)
                            <p class="mt-1 font-medium">{{ $comm->subject }}</p>
                        @endif
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $comm->message }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $comm->staffProfile?->full_name ?? __('Unassigned') }}
                            · {{ $commStatuses[$comm->status] ?? $comm->status }}
                            @if ($comm->relatedTicket)
                                · <a href="{{ route('tenants.show', ['tenant' => $tenant, 'tab' => 'support', 'ticket' => $comm->relatedTicket->id]) }}" class="text-indigo-600 hover:underline">#{{ $comm->relatedTicket->id }}</a>
                            @endif
                        </p>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No communication history yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
