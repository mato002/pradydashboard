@php
    $checklistVariant = fn (string $status): string => match ($status) {
        'pass' => 'success',
        'fail' => 'danger',
        'pending', 'skip' => 'warning',
        default => 'neutral',
    };

    $checklistLabel = fn (string $status): string => match ($status) {
        'pass' => __('Pass'),
        'fail' => __('Fail'),
        'pending' => __('Pending'),
        'skip' => __('Skipped'),
        default => ucfirst($status),
    };
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Quick setup checklist') }}</h3>
    <ul class="mt-4 space-y-2">
        @foreach ($checklist as $item)
            <li class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/40">
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $item['label'] }}</p>
                    @if (filled($item['message'] ?? null))
                        <p class="mt-1 text-xs text-slate-500">{{ $item['message'] }}</p>
                    @endif
                    @if (filled($item['action_url'] ?? null) && filled($item['action_label'] ?? null))
                        <a href="{{ $item['action_url'] }}" class="mt-2 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $item['action_label'] }}</a>
                    @endif
                </div>
                <x-ui.status-badge :variant="$checklistVariant($item['status'])">{{ $checklistLabel($item['status']) }}</x-ui.status-badge>
            </li>
        @endforeach
    </ul>
</div>
