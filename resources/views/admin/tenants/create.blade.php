@php
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    $textareaClass = $selectClass.' min-h-[80px]';
@endphp

<x-dashboard-layout :heading="__('Provision tenant')" :subheading="__('Add a new organization — only the essentials')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Tenant management') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Provision tenant') }}</h2>
                <p class="mt-1 max-w-xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Company, product, and plan — everything else can be added later from the tenant command center.') }}
                </p>
            </div>
            <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                {{ __('Back to tenants') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-100">
                <p class="font-semibold">{{ __('Please fix the following:') }}</p>
                <ul class="mt-2 list-inside list-disc text-xs">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('tenants.store') }}" class="space-y-6">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="space-y-8 p-6 sm:p-8">
                    <section class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Who is this tenant?') }}</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Primary organization and contact') }}</p>
                        </div>
                        @include('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'organization',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ])
                    </section>

                    <section class="space-y-4 border-t border-slate-200/80 pt-8 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Which product?') }}</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Hosted app and domain for license checks') }}</p>
                        </div>
                        @include('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'product',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ])
                    </section>

                    <section class="space-y-4 border-t border-slate-200/80 pt-8 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing') }}</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Pick a plan — currency, cycle, and trial status use sensible defaults') }}</p>
                        </div>
                        @include('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'billing',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ])
                    </section>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-950/50">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('KRA PIN, server, cPanel, and other ops details → tenant profile after save.') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('tenants.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200/60 dark:text-slate-300 dark:hover:bg-slate-800">
                            {{ __('Cancel') }}
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            {{ __('Provision tenant') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            document.getElementById('saas_plan_id')?.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (!opt?.dataset?.name) return;
                const planInput = document.getElementById('subscription_plan');
                const amountInput = document.getElementById('subscription_amount');
                if (planInput) planInput.value = opt.dataset.name;
                if (amountInput && opt.dataset.amount) amountInput.value = opt.dataset.amount;
            });
        </script>
    </div>
</x-dashboard-layout>
