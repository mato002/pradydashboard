@php
    $health = $monitoringSnapshot['health'] ?? [];
    $redis = $monitoringSnapshot['redis'] ?? [];
    $horizon = $monitoringSnapshot['horizon'] ?? [];
    $failedCount = (int) ($health['failed_jobs_count'] ?? 0);
    $pendingCount = (int) ($health['total_pending'] ?? 0);
    $redisOk = (bool) ($redis['available'] ?? false);
@endphp

<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Monitoring & queues') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Redis health, queue backlog, and observability shortcuts') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('monitoring.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                {{ __('Observability center') }}
            </a>
            <a href="{{ route('monitoring.queues') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-500">
                {{ __('Redis & Queues') }}
            </a>
        </div>
    </div>

    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Redis') }}</p>
            <p class="mt-1 text-xl font-semibold {{ $redisOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $redis['label'] ?? __('Unknown') }}
            </p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Horizon') }}</p>
            <p class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">{{ $horizon['label'] ?? __('Unknown') }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Pending jobs') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($pendingCount) }}</p>
            @if ($health['queues_clear'] ?? false)
                <p class="mt-0.5 text-[11px] text-emerald-600 dark:text-emerald-400">{{ __('Queues are clear.') }}</p>
            @endif
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Failed jobs') }}</p>
            <p class="mt-1 text-xl font-semibold tabular-nums {{ $failedCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ number_format($failedCount) }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">{{ __('Busiest queue') }}</p>
            @if ($health['busiest_queue'] ?? null)
                <p class="mt-1 font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $health['busiest_queue']['queue'] }}</p>
                <p class="text-[11px] text-slate-500">{{ number_format($health['busiest_queue']['pending']) }} {{ __('pending') }}</p>
            @else
                <p class="mt-1 text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ __('All clear') }}</p>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
        @permission('server_health.view')
            <a href="{{ route('server-health.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Server health') }}</a>
        @endpermission
        @permission('activity_logs.view')
            <a href="{{ route('activity-logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Activity logs') }}</a>
        @endpermission
        @permission('risk_center.view')
            <a href="{{ route('risk-center.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Risk center') }}</a>
        @endpermission
        @if ($monitoringSnapshot['horizon_enabled'] ?? false)
            <a href="{{ url('/'.trim((string) ($monitoringSnapshot['horizon_path'] ?? 'horizon'), '/')) }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Horizon') }}</a>
        @endif
    </div>
</div>
