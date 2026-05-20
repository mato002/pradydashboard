<x-dashboard-layout :heading="__('Register server')" :subheading="__('Add a Hostinger / WHM node to the fleet')">
    <div class="space-y-6 pb-12 max-w-4xl">
        <nav class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('servers.index') }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('Servers') }}</a>
            <span class="text-slate-300 dark:text-slate-600">/</span>
            <span class="text-indigo-600 dark:text-indigo-400">{{ __('Register') }}</span>
        </nav>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200">
                <p class="font-semibold">{{ __('Please correct the following:') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('servers.store') }}" id="server-form" class="space-y-5">
            @csrf
            @include('admin.servers.partials._form-tabs', [
                'server' => $server,
                'submitLabel' => __('Register server'),
            ])
        </form>
    </div>
</x-dashboard-layout>
