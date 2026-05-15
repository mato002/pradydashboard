@php
    $isEdit = $tenant->exists ?? false;
    $heading = $isEdit ? __('Edit tenant') : __('Provision tenant');
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    $textareaClass = $selectClass.' min-h-[80px]';
@endphp

<x-dashboard-layout :heading="$heading" :subheading="__('Onboard a new organization onto the platform')">
    <div class="mx-auto max-w-5xl space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Tenant management') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $heading }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Capture organization details, assign product & server, configure billing, and set infrastructure references for go-live.') }}
                        </p>
                    </div>
                    <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        {{ __('Back to tenants') }}
                    </a>
                </div>

                <form
                    method="post"
                    action="{{ $isEdit ? route('tenants.update', $tenant) : route('tenants.store') }}"
                    class="space-y-6"
                    x-data="{ step: 'organization' }"
                >
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="flex flex-wrap gap-2 border-b border-slate-200/80 pb-1 dark:border-slate-800">
                        @foreach (['organization' => __('Organization'), 'product' => __('Product & hosting'), 'billing' => __('Billing'), 'infrastructure' => __('Infrastructure')] as $key => $label)
                            <button
                                type="button"
                                class="rounded-t-lg px-4 py-2 text-sm font-semibold transition"
                                :class="step === '{{ $key }}' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:text-indigo-400 dark:ring-slate-700' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                                @click="step = '{{ $key }}'"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <div x-show="step === 'organization'" x-cloak class="space-y-6 p-6 sm:p-8">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Organization profile') }}</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Legal entity, contacts, and regional settings') }}</p>
                            </div>
                            @include('admin.tenants._form', [
                                'tenant' => $tenant,
                                'projects' => $projects,
                                'servers' => $servers,
                                'plans' => $plans ?? collect(),
                                'section' => 'organization',
                                'selectClass' => $selectClass,
                                'textareaClass' => $textareaClass,
                            ])
                        </div>

                        <div x-show="step === 'product'" x-cloak class="space-y-6 p-6 sm:p-8">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Product allocation') }}</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Hosted SaaS product, server placement, and tenant domain') }}</p>
                            </div>
                            @include('admin.tenants._form', [
                                'tenant' => $tenant,
                                'projects' => $projects,
                                'servers' => $servers,
                                'plans' => $plans ?? collect(),
                                'section' => 'product',
                                'selectClass' => $selectClass,
                                'textareaClass' => $textareaClass,
                            ])
                        </div>

                        <div x-show="step === 'billing'" x-cloak class="space-y-6 p-6 sm:p-8">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Subscription & billing') }}</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Plan, MRR, billing cycle, grace period, and lifecycle status') }}</p>
                            </div>
                            @include('admin.tenants._form', [
                                'tenant' => $tenant,
                                'projects' => $projects,
                                'servers' => $servers,
                                'plans' => $plans ?? collect(),
                                'section' => 'billing',
                                'selectClass' => $selectClass,
                                'textareaClass' => $textareaClass,
                            ])
                        </div>

                        <div x-show="step === 'infrastructure'" x-cloak class="space-y-6 p-6 sm:p-8">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Infrastructure references') }}</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('cPanel, database, deployment version, and internal notes') }}</p>
                            </div>
                            @include('admin.tenants._form', [
                                'tenant' => $tenant,
                                'projects' => $projects,
                                'servers' => $servers,
                                'plans' => $plans ?? collect(),
                                'section' => 'infrastructure',
                                'selectClass' => $selectClass,
                                'textareaClass' => $textareaClass,
                            ])
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-950/50">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Provisioning creates the tenant record and initial subscription row.') }}</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('tenants.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200/60 dark:text-slate-300 dark:hover:bg-slate-800">
                                    {{ __('Cancel') }}
                                </a>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    {{ $isEdit ? __('Save changes') : __('Provision tenant') }}
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
