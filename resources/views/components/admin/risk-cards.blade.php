@props([
    'risks',
    'title' => __('Operational risks'),
    'empty' => __('No risks detected for this record.'),
    'compact' => false,
])

@if ($risks->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900']) }}>
        @if ($title)
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            </div>
        @endif
        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            @foreach ($risks as $risk)
                @php
                    $severityClass = match ($risk['severity']) {
                        'critical' => 'border-rose-200 bg-rose-50/60 dark:border-rose-900 dark:bg-rose-950/30',
                        'warning' => 'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20',
                        default => 'border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950/30',
                    };
                    $muted = $risk['acknowledged'] ?? false;
                @endphp
                <li @class(['px-4 py-3 text-sm border-l-4', $severityClass, $muted ? 'opacity-50' : ''])>
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                @if (! empty($risk['url']))
                                    <a href="{{ $risk['url'] }}" class="hover:text-indigo-600 hover:underline">{{ $risk['title'] }}</a>
                                @else
                                    {{ $risk['title'] }}
                                @endif
                                @if ($muted)
                                    <span class="ms-1 text-[10px] uppercase text-gray-400">{{ __('Acknowledged') }}</span>
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $risk['description'] }}</p>
                            @unless ($compact)
                                <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-300">{{ $risk['recommended_action'] }}</p>
                            @endunless
                        </div>
                        @if (! $muted)
                            <form method="post" action="{{ route('risk-center.acknowledge') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="risk_key" value="{{ $risk['key'] }}" />
                                <button type="submit" class="rounded border px-2 py-1 text-[10px] font-semibold uppercase">{{ __('Acknowledge') }}</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@elseif (! $compact)
    <p class="text-sm text-gray-500">{{ $empty }}</p>
@endif
