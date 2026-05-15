@php
    $statusVariant = match ($profile['status'] ?? 'active') {
        'active' => 'success',
        'revoked', 'expired' => 'danger',
        default => 'warning',
    };
@endphp

<x-dashboard-layout :heading="$profile['name']" :subheading="$profile['project']">
    <x-admin.form-shell
        :title="$profile['name']"
        :subtitle="__('Project: :project', ['project' => $profile['project']])"
        :badge="__('API key')"
        :back-href="route('api-credentials.index')"
        :back-label="__('Back to API center')"
    >
        <x-slot name="actions">
            <a href="{{ route('api-credentials.keys.edit', $key) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                {{ __('Edit') }}
            </a>
        </x-slot>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <x-admin.form-section :title="__('Token')" :description="__('Masked credential identifier.')">
                    <div class="rounded-xl border border-slate-200/80 bg-slate-950 px-4 py-3 font-mono text-sm text-emerald-400 dark:border-slate-700">
                        {{ $profile['masked_token'] }}
                    </div>
                </x-admin.form-section>

                <x-admin.form-section :title="__('Scopes')" :description="__('Granted permissions for this credential.')">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($profile['scopes'] ?? [] as $scope)
                            <span class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $scope }}</span>
                        @endforeach
                    </div>
                </x-admin.form-section>
            </div>

            <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Metadata') }}</h3>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-ui.status-badge :variant="$statusVariant">{{ ucfirst($profile['status']) }}</x-ui.status-badge></dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Tenant scope') }}</dt><dd class="font-medium">{{ $profile['tenant'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Rate limit') }}</dt><dd class="font-medium">{{ $profile['rate_limit'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last used') }}</dt><dd class="font-medium">{{ $profile['last_used'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Expires') }}</dt><dd class="font-medium">{{ $profile['expiry'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Created') }}</dt><dd class="font-medium">{{ $profile['created'] }}</dd></div>
                </div>
            </dl>
        </div>
    </x-admin.form-shell>
</x-dashboard-layout>
