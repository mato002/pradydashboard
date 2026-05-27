@if (! empty($recommendedActions))
    <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/50 p-5 shadow-card dark:border-indigo-900 dark:bg-indigo-950/20">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Recommended next actions') }}</h3>
        <div class="mt-4 space-y-3">
            @foreach ($recommendedActions as $action)
                <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $action['label'] }}</p>
                                <x-ui.status-badge :variant="$riskVariant($action['risk_level'] ?? 'medium')">{{ ucfirst($action['risk_level'] ?? 'medium') }}</x-ui.status-badge>
                                @unless ($action['available'] ?? false)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Unavailable in dashboard') }}</span>
                                @endunless
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $action['reason'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            @if (($action['available'] ?? false) && ($action['action_type'] ?? '') === 'post')
                                @permission('payments_gateway.manage')
                                    <form method="post" action="{{ $action['action_url'] }}" class="flex flex-col items-end gap-2" onsubmit="return confirm(@js($action['confirm'] ?? __('Run this action?')))">
                                        @csrf
                                        @if ($action['accepts_comments'] ?? false)
                                            <input type="text" name="comments" maxlength="500" placeholder="{{ __('Comments (optional)') }}" class="w-full min-w-[12rem] rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900">
                                        @endif
                                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ $action['label'] }}</button>
                                    </form>
                                @endpermission
                            @elseif (($action['available'] ?? false) && ($action['action_type'] ?? '') === 'navigate' && filled($action['action_url'] ?? null))
                                <a href="{{ $action['action_url'] }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Open') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
