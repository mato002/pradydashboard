<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Project API keys') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Keys Prady issues for license checks, webhooks, and tenant sync.') }}</p>
        </div>
        <a href="{{ route('api-credentials.keys.create') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">+ {{ __('Generate key') }}</a>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
        @forelse ($keys as $key)
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $key['name'] }}</p>
                    <p class="font-mono text-xs text-slate-500">{{ $key['masked_token'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $key['project'] }} · {{ $key['tenants_count'] }} {{ __('tenants') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.status-badge :variant="$key['status'] === 'active' ? 'success' : 'warning'">{{ $key['status'] }}</x-ui.status-badge>
                    <a href="{{ route('api-credentials.keys.show', $key['id']) }}" class="text-xs font-semibold text-indigo-600">{{ __('View') }}</a>
                </div>
            </div>
        @empty
            <p class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No project API keys issued yet.') }}</p>
        @endforelse
    </div>
</div>
