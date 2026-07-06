<x-dashboard-layout :heading="__('SSL & Domains')" :subheading="$domain->domain">
    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $domain->domain }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Certificate and routing details') }}</p>
            </div>
            <a href="{{ route('ssl-domains.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                {{ __('Back to domains') }}
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Certificate') }}</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $domain->ssl_status ?? 'unknown') }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Issuer') }}</dt><dd class="font-medium">{{ $domain->ssl_issuer ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Expires') }}</dt><dd class="font-medium">{{ $domain->ssl_expires_at?->format('M j, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Auto renew') }}</dt><dd class="font-medium">{{ $domain->auto_renew ? __('Enabled') : __('Disabled') }}</dd></div>
                </dl>
            </div>

            <div id="routing" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Routing') }}</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Target') }}</dt><dd class="font-medium">{{ $domain->routing_target ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Server') }}</dt><dd class="font-medium">{{ $domain->server?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Tenant') }}</dt><dd class="font-medium">{{ $domain->tenant?->company_name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('DNS status') }}</dt><dd class="font-medium capitalize">{{ $domain->dns_status ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('ssl-domains.domain.renew', $domain) }}">
                @csrf
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    {{ __('Force renewal') }}
                </button>
            </form>
            <form method="POST" action="{{ route('ssl-domains.verify-dns') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    {{ __('Verify DNS fleet-wide') }}
                </button>
            </form>
        </div>
    </div>
</x-dashboard-layout>
