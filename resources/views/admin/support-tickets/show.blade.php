@php
    $ref = $profile['db_id'] ?? $profile['id'];
    $priorityVariant = match ($profile['priority'] ?? 'medium') {
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        default => 'neutral',
    };
    $statusVariant = match ($profile['status'] ?? 'open') {
        'resolved', 'closed' => 'success',
        'escalated' => 'danger',
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
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Conversation') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100 p-5 dark:divide-slate-800">
                        @forelse ($conversation as $msg)
                            <div class="py-3">
                                <p class="text-xs font-semibold text-slate-500">{{ $msg['author'] ?? __('Agent') }} · {{ $msg['time'] ?? '' }}</p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $msg['body'] ?? $msg }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No public messages yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Details') }}</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Assigned') }}</dt><dd class="font-medium">{{ $profile['assigned_to'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('SLA') }}</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $profile['sla_status']) }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last response') }}</dt><dd class="font-medium">{{ $profile['last_response'] }}</dd></div>
                        @if (! empty($profile['project']))
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Project') }}</dt><dd class="font-medium">{{ $profile['project'] }}</dd></div>
                        @endif
                    </div>
                </dl>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('SLA progress') }}</p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ $profile['sla_progress'] ?? 0 }}%"></div>
                    </div>
                    <p class="mt-1 text-right text-xs font-semibold text-slate-600">{{ $profile['sla_progress'] ?? 0 }}%</p>
                </div>
            </div>
        </div>
    </x-admin.form-shell>
</x-dashboard-layout>
