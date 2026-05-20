@php
    $ref = $ticket->id;
    $priorityVariant = match ($profile['priority'] ?? 'medium') {
        'urgent', 'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        default => 'neutral',
    };
    $statusVariant = match ($profile['status'] ?? 'open') {
        'resolved', 'closed' => 'success',
        'in_progress' => 'warning',
        default => 'info',
    };
@endphp

<x-dashboard-layout :heading="$profile['id']" :subheading="$profile['subject']">
    <x-admin.form-shell
        :title="$profile['subject']"
        :subtitle="__('Tenant: :tenant', ['tenant' => $profile['tenant']])"
        :badge="__('Support ticket')"
        :back-href="route('support-tickets.index')"
        :back-label="__('Back to queue')"
    >
        <x-slot name="actions">
            <a href="{{ route('support-tickets.edit', $ref) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                {{ __('Edit') }}
            </a>
        </x-slot>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.status-badge :variant="$priorityVariant">{{ ucfirst($profile['priority']) }}</x-ui.status-badge>
                            <x-ui.status-badge :variant="$statusVariant">{{ ucfirst(str_replace('_', ' ', $profile['status'])) }}</x-ui.status-badge>
                            <x-ui.status-badge variant="neutral">{{ $profile['category'] }}</x-ui.status-badge>
                        </div>
                    </div>
                    <div class="p-5 text-sm text-slate-600 dark:text-slate-300">
                        <p>{{ $profile['description'] ?? __('No additional description provided.') }}</p>
                        @if (! empty($profile['resolution_notes']))
                            <p class="mt-3 rounded-lg bg-emerald-50 p-3 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-100">
                                <span class="text-xs font-semibold uppercase">{{ __('Resolution') }}</span><br>
                                {{ $profile['resolution_notes'] }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Timeline') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100 p-5 dark:divide-slate-800">
                        @forelse ($ticket->comments as $msg)
                            <div class="py-3">
                                <p class="text-xs font-semibold text-slate-500">
                                    {{ $msg->authorName() }} · {{ $msg->created_at->diffForHumans() }}
                                    · {{ $commentTypes[$msg->comment_type] ?? $msg->comment_type }}
                                </p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $msg->message }}</p>
                                <span class="text-[10px] uppercase text-slate-400">{{ $visibilities[$msg->visibility] ?? $msg->visibility }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No comments yet.') }}</p>
                        @endforelse
                    </div>
                    <form method="post" action="{{ route('support-tickets.comments.store', $ticket) }}" class="border-t border-slate-200/80 p-5 dark:border-slate-800/80">
                        @csrf
                        <textarea name="message" rows="2" required class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="{{ __('Add a comment…') }}"></textarea>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <select name="comment_type" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                                @foreach ($commentTypes as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                            </select>
                            <select name="visibility" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                                @foreach ($visibilities as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                            </select>
                        </div>
                        <button type="submit" class="mt-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Post') }}</button>
                    </form>
                </div>
            </div>

            <div class="space-y-4">
                <x-admin.assigned-staff :assignments="$staffAssignments" :title="__('Assigned staff')" />

                <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Details') }}</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Assigned') }}</dt><dd class="font-medium">{{ $profile['assigned_to'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Opened') }}</dt><dd class="font-medium">{{ $profile['opened_at'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last update') }}</dt><dd class="font-medium">{{ $profile['last_response'] }}</dd></div>
                        @if (! empty($profile['project']))
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Project') }}</dt><dd class="font-medium">{{ $profile['project'] }}</dd></div>
                        @endif
                        @if ($ticket->tenant_id)
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">{{ __('Tenant') }}</dt>
                                <dd class="font-medium">
                                    <a href="{{ route('tenants.show', ['tenant' => $ticket->tenant_id, 'tab' => 'support', 'ticket' => $ticket->id]) }}" class="text-indigo-600 hover:underline">{{ $profile['tenant'] }}</a>
                                </dd>
                            </div>
                        @endif
                    </div>
                </dl>
            </div>
        </div>

        <x-admin.activity-feed :logs="$activityLogs" class="mt-6" />
    </x-admin.form-shell>
</x-dashboard-layout>
