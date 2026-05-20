@props([
    'logs',
    'empty' => __('No activity recorded yet.'),
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900']) }}>
    @if (! $compact)
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Activity') }}</h3>
        </div>
    @endif
    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
        @forelse ($logs as $log)
            <li class="px-4 py-3 text-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $log->description }}</p>
                    <time class="shrink-0 text-xs text-gray-500" datetime="{{ $log->created_at?->toIso8601String() }}">
                        {{ $log->created_at?->diffForHumans() }}
                    </time>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $log->actorDisplayName() }}
                    · <span class="capitalize">{{ $log->categoryLabel() }}</span>
                    · <span class="font-mono text-[10px]">{{ $log->action }}</span>
                </p>
                @if ($log->old_values || $log->new_values)
                    <details class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                        <summary class="cursor-pointer font-medium">{{ __('Change details') }}</summary>
                        @if ($log->old_values)
                            <p class="mt-1"><span class="font-semibold">{{ __('Before') }}:</span> {{ json_encode($log->old_values) }}</p>
                        @endif
                        @if ($log->new_values)
                            <p class="mt-1"><span class="font-semibold">{{ __('After') }}:</span> {{ json_encode($log->new_values) }}</p>
                        @endif
                    </details>
                @endif
            </li>
        @empty
            <li class="px-4 py-8 text-center text-sm text-gray-500">{{ $empty }}</li>
        @endforelse
    </ul>
</div>
