@php
    $statusVariant = fn (string $s): string => match ($s) {
        'successful' => 'success',
        'running' => 'info',
        'failed' => 'danger',
        'queued' => 'neutral',
        'warning' => 'warning',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="$backup->name" :subheading="__('Backup details, download, and action history')">
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('backups.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">{{ __('← Back to backups') }}</a>
            <div class="flex flex-wrap gap-2">
                @if ($backup->hasDownloadableArchive())
                    <a href="{{ route('backups.download', $backup) }}"
                       class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        {{ __('Download archive') }}
                    </a>
                @endif
                @if (in_array($backup->status, ['successful', 'warning'], true))
                    <form method="POST" action="{{ route('backups.verify', $backup) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            {{ __('Verify integrity') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Backup summary') }}</h2>
                <dl class="mt-3 grid gap-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-ui.status-badge :variant="$statusVariant($backup->status)">{{ ucfirst($backup->status) }}</x-ui.status-badge></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $backup->backup_type }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Size') }}</dt><dd>{{ $backup->formattedSize() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Server') }}</dt><dd>{{ $backup->server?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Tenant') }}</dt><dd>{{ $backup->tenant?->company_name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Started') }}</dt><dd>{{ $backup->started_at?->toDateTimeString() ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Completed') }}</dt><dd>{{ $backup->completed_at?->toDateTimeString() ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Integrity') }}</dt><dd>{{ $backup->integrity_verified ? __('Verified') : __('Not verified') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Archive') }}</dt><dd class="font-mono text-xs">{{ $backup->archive_path ?? __('Not available') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Checksum') }}</dt><dd class="font-mono text-xs break-all">{{ $backup->checksum ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Action log') }}</h2>
                @if ($backup->actions->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">{{ __('No actions recorded for this backup yet.') }}</p>
                @else
                    <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($backup->actions as $action)
                            <li class="py-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $action->action }}</span>
                                    <span class="text-xs text-slate-500">{{ $action->performed_at?->toDateTimeString() }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $action->actor_label ?? __('system') }}
                                    · {{ ucfirst($action->result) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        @if ($backup->notes)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Notes') }}</h2>
                <pre class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300">{{ $backup->notes }}</pre>
            </div>
        @endif
    </div>
</x-dashboard-layout>
