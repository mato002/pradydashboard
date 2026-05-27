@php
    $toneClasses = [
        'success' => 'border-emerald-200/80 bg-emerald-50/50 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-100',
        'warning' => 'border-amber-200/80 bg-amber-50/50 text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100',
        'danger' => 'border-rose-200/80 bg-rose-50/50 text-rose-950 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-100',
        'neutral' => 'border-slate-200/80 bg-white text-slate-900 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-100',
    ];
    $iconPaths = [
        'currency' => 'M12 6v12m-3-2.25h6M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'receipt' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5',
        'server' => 'M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3V6.75a3 3 0 013-3h13.5a3 3 0 013 3v4.5a3 3 0 01-3 3m-16.5 0v1.5',
        'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'archive' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'code' => 'M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5',
        'key' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
        'link' => 'M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244',
        'life-buoy' => 'M16.712 4.33a9.027 9.027 0 011.651 2.343m-11.32 0a9.027 9.027 0 00-1.651-2.343M12 21a9 9 0 100-18 9 9 0 000 18zm0 0v-3.375',
        'layers' => 'M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3',
        'grid' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z',
        'wallet' => 'M21 12a2.25 2.25 0 00-2.25-2.25H15a2.25 2.25 0 01-2.25-2.25v-1.5a2.25 2.25 0 012.25-2.25H18.75A2.25 2.25 0 0121 9.75v10.5A2.25 2.25 0 0118.75 22.5H5.25A2.25 2.25 0 013 20.25V9.75',
        'credit-card' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h16.5',
        'alert-triangle' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'git-branch' => 'M4.5 12.75l6 6 9-13.5',
        'plug' => 'M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h16.5A2.25 2.25 0 0022.5 19.5v-1.5a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v1.5a2.25 2.25 0 002.25 2.25z',
        'unlink' => 'M13.19 8.688a4.5 4.5 0 00-7.757 4.243m7.5-4.243a4.5 4.5 0 01-7.757 4.243',
        'beaker' => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714',
        'file-text' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'file-warning' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    ];
@endphp

<div class="mb-6 space-y-4">
    @foreach ($metricGroups as $group)
        <section>
            <h3 class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $group['label'] }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @foreach ($group['items'] as $item)
                    @php $tone = $toneClasses[$item['tone']] ?? $toneClasses['neutral']; @endphp
                    <div class="rounded-2xl border p-3.5 shadow-sm {{ $tone }}">
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/60 dark:bg-black/20">
                                <svg class="h-4 w-4 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPaths[$item['icon']] ?? $iconPaths['grid'] }}" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium leading-snug text-slate-600 dark:text-slate-300">{{ $item['label'] }}</p>
                                <p class="mt-1 text-sm font-semibold leading-snug tabular-nums">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($opsSummary['public_url'] ?? null)
        <p class="truncate text-xs text-indigo-600 dark:text-indigo-400">
            <a href="{{ $opsSummary['public_url'] }}" target="_blank" rel="noopener noreferrer">{{ $opsSummary['public_url'] }}</a>
        </p>
    @endif
</div>
