@props([
    'center',
])

@php
    $summary = $center['summary'] ?? [];
    $sections = $center['sections'] ?? [];
    $total = (int) ($center['total'] ?? 0);

    $chipTones = [
        'rose' => 'border-rose-200/80 bg-rose-50/80 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200',
        'amber' => 'border-amber-200/80 bg-amber-50/80 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100',
        'yellow' => 'border-yellow-200/80 bg-yellow-50/80 text-yellow-900 dark:border-yellow-900/60 dark:bg-yellow-950/30 dark:text-yellow-100',
        'sky' => 'border-sky-200/80 bg-sky-50/80 text-sky-900 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-100',
        'violet' => 'border-violet-200/80 bg-violet-50/80 text-violet-900 dark:border-violet-900/60 dark:bg-violet-950/40 dark:text-violet-100',
        'indigo' => 'border-indigo-200/80 bg-indigo-50/80 text-indigo-900 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-100',
        'slate' => 'border-slate-200/80 bg-slate-50/80 text-slate-800 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200',
    ];

    $summaryLabels = [
        'critical' => __('Critical risks'),
        'high' => __('High risks'),
        'medium' => __('Medium risks'),
        'infrastructure' => __('Infrastructure'),
        'billing' => __('Billing & collections'),
        'licensing' => __('Licensing & subscriptions'),
        'support' => __('Support escalations'),
    ];
@endphp

<div
    {{ $attributes->merge(['class' => 'mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60']) }}
    x-data="{
        openSections: @js(collect($sections)->where('count', '>', 0)->pluck('id')->values()->all()),
        toggle(id) {
            if (this.openSections.includes(id)) {
                this.openSections = this.openSections.filter(s => s !== id);
            } else {
                this.openSections.push(id);
            }
        },
        isOpen(id) { return this.openSections.includes(id); }
    }"
>
    <div class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/95 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/95">
        <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-rose-600 dark:text-rose-400">{{ __('Operations Risk Center') }}</p>
                <h2 class="mt-0.5 text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Attention required') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ trans_choice(':count open risk needs review|:count open risks need review', $total, ['count' => $total]) }}
                </p>
            </div>
            <a href="{{ route('risk-center.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200/80 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-300 dark:hover:bg-slate-700">
                {{ __('Full Risk Center') }}
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2 border-t border-slate-100/80 px-4 py-3 dark:border-slate-800/80 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($summaryLabels as $key => $label)
                @php
                    $chip = $summary[$key] ?? ['count' => 0, 'label' => __('Clear'), 'tone' => 'slate'];
                    $toneClass = $chipTones[$chip['tone']] ?? $chipTones['slate'];
                @endphp
                <div @class(['rounded-xl border px-2.5 py-2', $toneClass])>
                    <p class="text-[10px] font-semibold uppercase tracking-wide opacity-80">{{ $label }}</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums">{{ $chip['count'] }}</p>
                    <p class="mt-0.5 text-[10px] font-medium opacity-75">{{ $chip['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
        @foreach ($sections as $section)
            <section>
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30"
                    @click="toggle(@js($section['id']))"
                    :aria-expanded="isOpen(@js($section['id']))"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold tabular-nums text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            :class="@js($section['count']) > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'"
                        >{{ $section['count'] }}</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $section['label'] }}</h3>
                            @if ($section['count'] === 0)
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $section['empty'] }}</p>
                            @else
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ trans_choice(':count item|:count items', $section['count'], ['count' => $section['count']]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="isOpen(@js($section['id'])) ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div
                    x-show="isOpen(@js($section['id']))"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="border-t border-slate-100/80 dark:border-slate-800/80"
                >
                    @if ($section['count'] === 0)
                        <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ $section['empty'] }}</p>
                    @else
                        <div class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                            @foreach ($section['items'] as $item)
                                @if ($item['type'] === 'bundle')
                                    <div x-data="{ expanded: false }" class="bg-slate-50/40 dark:bg-slate-950/20">
                                        <x-admin.risk-item-card
                                            :severity="$item['severity']"
                                            :severity-label="$item['severity_label']"
                                            :title="$item['title']"
                                            :description="$item['subtitle']"
                                            :time-label="$item['time_label'] ?? null"
                                            :actions="[]"
                                        >
                                            <x-slot name="context">
                                                <button
                                                    type="button"
                                                    @click="expanded = !expanded"
                                                    class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                                >
                                                    <span x-text="expanded ? @js(__('Hide details')) : @js(__('Show affected'))"></span>
                                                    <svg class="h-3.5 w-3.5 transition" :class="expanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                </button>
                                                <div
                                                    x-show="expanded"
                                                    x-cloak
                                                    x-transition
                                                    class="mt-3 space-y-2"
                                                >
                                                    @foreach ($item['risks'] as $risk)
                                                        <x-admin.risk-item-card
                                                            nested
                                                            :severity="$risk['severity']"
                                                            :severity-label="$risk['severity_label']"
                                                            :title="$risk['title']"
                                                            :description="$risk['description']"
                                                            :entity="$risk['entity_label'] ?? null"
                                                            :time-label="$risk['time_label'] ?? null"
                                                            :url="$risk['url'] ?? null"
                                                            :actions="$risk['actions'] ?? []"
                                                            :risk-key="$risk['key']"
                                                        />
                                                    @endforeach
                                                </div>
                                            </x-slot>
                                        </x-admin.risk-item-card>
                                    </div>
                                @else
                                    @php $risk = $item['risk']; @endphp
                                    <x-admin.risk-item-card
                                        :severity="$risk['severity']"
                                        :severity-label="$risk['severity_label']"
                                        :title="$risk['title']"
                                        :description="$risk['description']"
                                        :entity="$risk['entity_label'] ?? null"
                                        :time-label="$risk['time_label'] ?? null"
                                        :url="$risk['url'] ?? null"
                                        :actions="$risk['actions'] ?? []"
                                        :risk-key="$risk['key']"
                                    />
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</div>
