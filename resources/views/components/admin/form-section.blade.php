@props([
    'title',
    'description' => null,
    'step' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60']) }}>
    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div class="flex items-start gap-3">
            @if ($step)
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white shadow-lg shadow-indigo-500/25">{{ $step }}</span>
            @endif
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                @if ($description)
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="p-5">
        {{ $slot }}
    </div>
</section>
