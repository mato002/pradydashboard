@php
    $meta = old('meta', []);
    $domainsText = old('hosted_domains_text', '');
@endphp

<div class="border-b border-slate-100/90 px-4 py-3 dark:border-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing') }}</h3>
            <p class="text-[11px] text-slate-500">{{ __('Spend, renewals, and tenant allocation') }}</p>
        </div>
        <p class="text-sm font-semibold tabular-nums text-amber-700 dark:text-amber-300">
            <span x-text="form.currency || 'KES'"></span> <span x-text="estimatedCost">0.00</span><span class="text-[10px] font-normal text-slate-500">/mo</span>
        </p>
    </div>
</div>
<div class="space-y-4 p-4 sm:p-5">
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Projected annual') }}</p>
            <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">
                <span x-text="form.currency || 'KES'"></span> <span x-text="projectedAnnualCost">0.00</span>
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-950/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Tenant revenue') }}</p>
            <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">
                <span x-text="form.currency || 'KES'"></span> <span x-text="(parseFloat(form.monthly_revenue) || 0).toFixed(2)">0.00</span>
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 p-3 dark:border-slate-700 dark:bg-slate-950/50" :class="profitabilityEstimate.positive ? 'ring-1 ring-emerald-500/25' : 'ring-1 ring-rose-500/25'">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Profitability est.') }}</p>
            <p class="mt-1 text-lg font-semibold tabular-nums" :class="profitabilityEstimate.positive ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'">
                <span x-text="form.currency || 'KES'"></span> <span x-text="profitabilityEstimate.margin">0.00</span>
                <span class="text-xs font-normal text-slate-500">(<span x-text="profitabilityEstimate.pct"></span>%)</span>
            </p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-admin.infra-field :label="__('Currency (ISO)')" name="currency" :maxlength="3" :value="old('currency', 'KES')" model="form.currency" />
        <x-admin.infra-field :label="__('Monthly cost')" name="monthly_cost" type="number" step="0.01" model="form.monthly_cost" />
        <x-admin.infra-field :label="__('Monthly revenue (allocated)')" name="monthly_revenue" type="number" step="0.01" model="form.monthly_revenue" />
        <x-admin.infra-field :label="__('Renewal / expiry date')" name="renewal_expires_at" type="date" model="form.renewal_expires_at" />
        <x-admin.infra-field :label="__('Billing cycle')" name="meta[billing_cycle]" type="select" :value="$meta['billing_cycle'] ?? 'monthly'">
            <option value="monthly">{{ __('Monthly') }}</option>
            <option value="annual">{{ __('Annual') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Provider invoice ref')" name="meta[provider_invoice_ref]" :value="$meta['provider_invoice_ref'] ?? ''" />
        <x-admin.infra-field :label="__('Tenant allocation')" name="meta[tenant_allocation]" :value="$meta['tenant_allocation'] ?? ''" placeholder="Shared / dedicated" />
        <x-admin.infra-field :label="__('Usage alert %')" name="meta[usage_threshold]" type="number" :min="0" :max="100" :value="$meta['usage_threshold'] ?? '85'" />
        <div class="sm:col-span-2">
            <x-admin.infra-field :label="__('Hosted domains (one per line)')" name="hosted_domains_text" type="textarea" :rows="3" :value="$domainsText" />
        </div>
    </div>
</div>
