@php
    $overdueFollowUps = $supportSummary['overdue_follow_ups'] ?? 0;
@endphp

<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Support & communications') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Operational queue from live database records') }}</p>
        </div>
        <a href="{{ route('support-tickets.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open support center') }}</a>
    </div>
    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Open tickets') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums">{{ $supportSummary['open_tickets'] }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Urgent') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-rose-600">{{ $supportSummary['urgent_tickets'] }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Overdue tickets') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-amber-600">{{ $supportSummary['overdue_tickets'] }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Overdue follow-ups') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-rose-600">{{ $overdueFollowUps }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Tenants with open issues') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums">{{ $supportSummary['tenants_with_open_issues'] }}</p>
        </div>
    </div>
    @if (($supportSummary['recent_communications'] ?? collect())->isNotEmpty())
        <div class="border-t border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
            <p class="mb-2 text-xs font-semibold uppercase text-slate-500">{{ __('Recent communications') }}</p>
            <ul class="space-y-2 text-sm">
                @foreach ($supportSummary['recent_communications']->take(5) as $comm)
                    <li class="flex flex-wrap justify-between gap-2">
                        <span>
                            <a href="{{ route('tenants.show', ['tenant' => $comm->tenant_id, 'tab' => 'communications']) }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                {{ $comm->tenant?->company_name ?? __('Tenant') }}
                            </a>
                            — {{ \Illuminate\Support\Str::limit($comm->message, 60) }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $comm->communication_date->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
