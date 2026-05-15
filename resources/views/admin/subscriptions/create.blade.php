@php
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
@endphp

<x-dashboard-layout :heading="__('Add subscription')" :subheading="__('Assign a billing plan to an existing tenant')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Billing') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('New subscription') }}</h2>
                <p class="mt-1 max-w-xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Link a tenant to a SaaS plan, set billing period, and sync renewal dates to the tenant profile.') }}
                </p>
            </div>
            <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                {{ __('Back to subscriptions') }}
            </a>
        </div>

        <form
            method="post"
            action="{{ route('subscriptions.store') }}"
            class="space-y-6"
            x-data="{
                applyPlan() {
                    const sel = document.getElementById('saas_plan_id');
                    const opt = sel?.selectedOptions?.[0];
                    if (!opt || !opt.value) return;
                    const name = opt.dataset.name;
                    const monthly = opt.dataset.monthly;
                    const annual = opt.dataset.annual;
                    const cycle = document.getElementById('billing_cycle')?.value || 'monthly';
                    if (name) document.getElementById('plan_name').value = name;
                    const amount = cycle === 'annual' && annual ? annual : monthly;
                    if (amount) document.getElementById('amount').value = amount;
                }
            }"
        >
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-violet-50/80 to-fuchsia-50/40 px-6 py-4 dark:border-slate-800 dark:from-violet-950/30 dark:to-fuchsia-950/20">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant & plan') }}</h3>
                </div>
                <div class="grid gap-5 p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="tenant_id" :value="__('Tenant organization')" />
                        <select id="tenant_id" name="tenant_id" class="{{ $selectClass }}" required>
                            <option value="">{{ __('Select tenant…') }}</option>
                            @foreach ($tenants as $tenant)
                                <option
                                    value="{{ $tenant->id }}"
                                    data-product="{{ $tenant->project?->name }}"
                                    @selected(old('tenant_id') == $tenant->id)
                                >
                                    {{ $tenant->company_name }} — {{ $tenant->project?->name ?? __('No product') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tenant_id')" />
                        @if ($tenants->isEmpty())
                            <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                {{ __('No tenants yet.') }}
                                <a href="{{ route('tenants.create') }}" class="font-semibold underline">{{ __('Provision a tenant') }}</a>
                            </p>
                        @endif
                    </div>

                    @if ($plans->isNotEmpty())
                        <div class="sm:col-span-2">
                            <x-input-label for="saas_plan_id" :value="__('SaaS plan')" />
                            <select id="saas_plan_id" name="saas_plan_id" class="{{ $selectClass }}" @change="applyPlan()">
                                <option value="">{{ __('Custom / manual') }}</option>
                                @foreach ($plans as $plan)
                                    <option
                                        value="{{ $plan->id }}"
                                        data-name="{{ $plan->name }}"
                                        data-monthly="{{ $plan->monthly_price }}"
                                        data-annual="{{ $plan->annual_price }}"
                                        @selected(old('saas_plan_id') == $plan->id)
                                    >
                                        {{ $plan->name }} — {{ $plan->formattedMonthly() }}/mo
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('saas_plan_id')" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="plan_name" :value="__('Plan label')" />
                        <x-text-input id="plan_name" name="plan_name" type="text" class="mt-1 block w-full" :value="old('plan_name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('plan_name')" />
                    </div>

                    <div>
                        <x-input-label for="product_name" :value="__('Product name')" />
                        <x-text-input id="product_name" name="product_name" type="text" class="mt-1 block w-full" :value="old('product_name')" placeholder="{{ __('From tenant project') }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('product_name')" />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-6 py-4 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing terms') }}</h3>
                </div>
                <div class="grid gap-5 p-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>

                    <div>
                        <x-input-label for="billing_cycle" :value="__('Billing cycle')" />
                        <select id="billing_cycle" name="billing_cycle" class="{{ $selectClass }}" required @change="applyPlan()">
                            @foreach (['monthly', 'annual'] as $cycle)
                                <option value="{{ $cycle }}" @selected(old('billing_cycle', 'monthly') === $cycle)>{{ ucfirst($cycle) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />
                    </div>

                    <div>
                        <x-input-label for="current_period_start" :value="__('Period start')" />
                        <x-text-input id="current_period_start" name="current_period_start" type="date" class="mt-1 block w-full" :value="old('current_period_start', now()->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('current_period_start')" />
                    </div>

                    <div>
                        <x-input-label for="current_period_end" :value="__('Period end / renewal')" />
                        <x-text-input id="current_period_end" name="current_period_end" type="date" class="mt-1 block w-full" :value="old('current_period_end', now()->addMonth()->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('current_period_end')" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Subscription status')" />
                        <select id="status" name="status" class="{{ $selectClass }}" required>
                            @foreach (['active', 'trial', 'grace_period', 'overdue', 'suspended', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="auto_renew" value="1" class="rounded border-slate-300 text-violet-600 focus:ring-violet-500 dark:border-slate-600" @checked(old('auto_renew', true)) />
                            {{ __('Auto-renew') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:brightness-110" @disabled($tenants->isEmpty())>
                    {{ __('Create subscription') }}
                </button>
                <a href="{{ route('subscriptions.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-dashboard-layout>
