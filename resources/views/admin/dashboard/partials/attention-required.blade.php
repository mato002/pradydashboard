@if ($attentionRisks->isNotEmpty())
    <div class="mb-6 overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/90 via-white to-rose-50/60 p-4 shadow-card dark:border-amber-900/40 dark:from-amber-950/30 dark:via-slate-900 dark:to-rose-950/20">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Attention required') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Operational risks from billing, infrastructure, and support') }}</p>
            </div>
            <a href="{{ route('risk-center.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('Open Risk Center') }} →</a>
        </div>
        <x-admin.risk-cards :risks="$attentionRisks" :title="null" :compact="true" class="mt-4 border-0 bg-transparent shadow-none" />
    </div>
@endif
