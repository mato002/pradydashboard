@php
    $currency = $billingKpi['currency'] ?? 'KES';
@endphp

<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('MRR (subscriptions)') }}</p>
        <p class="mt-2 text-xl font-semibold tabular-nums">{{ $currency }} {{ number_format($billingKpi['mrr'] ?? 0, 2) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Outstanding') }}</p>
        <p class="mt-2 text-xl font-semibold tabular-nums">{{ $currency }} {{ number_format($billingKpi['outstanding'] ?? 0, 2) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Next renewal') }}</p>
        <p class="mt-2 text-lg font-semibold">{{ optional($billingKpi['next_renewal'] ?? null)->toFormattedDateString() ?? '—' }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Next invoice due') }}</p>
        <p class="mt-2 text-lg font-semibold">{{ optional($billingKpi['next_due'] ?? null)->toFormattedDateString() ?? '—' }}</p>
    </div>
</div>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Centralized billing from project subscriptions, modules, integrations, and usage.') }}</p>
    @if ($billableSubscriptions->isNotEmpty())
        <form method="post" action="{{ route('tenants.billing.generate-draft', $tenant) }}">
            @csrf
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('Generate draft invoice') }}
            </button>
        </form>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <form method="post" action="{{ route('tenants.billing-profile.update', $tenant) }}" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('PUT')
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Billing profile') }}</h3>
        </div>
        <div class="space-y-3 p-4 text-sm">
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Contact name') }}</label>
                <input name="billing_contact_name" value="{{ old('billing_contact_name', $tenant->billing_contact_name) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Billing email') }}</label>
                <input type="email" name="billing_email" value="{{ old('billing_email', $tenant->billing_email) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Phone') }}</label>
                <input name="billing_phone" value="{{ old('billing_phone', $tenant->billing_phone) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Address') }}</label>
                <textarea name="billing_address" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('billing_address', $tenant->billing_address) }}</textarea>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('Tax PIN') }}</label>
                    <input name="billing_tax_pin" value="{{ old('billing_tax_pin', $tenant->billing_tax_pin) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('Preferred currency') }}</label>
                    <input name="billing_preferred_currency" maxlength="3" value="{{ old('billing_preferred_currency', $tenant->billing_preferred_currency) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Payment terms') }}</label>
                <input name="billing_payment_terms" value="{{ old('billing_payment_terms', $tenant->billing_payment_terms) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="billing_tax_exempt" value="1" @checked(old('billing_tax_exempt', $tenant->billing_tax_exempt)) class="rounded border-gray-300" />
                <span>{{ __('Tax exempt') }}</span>
            </label>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('Notes') }}</label>
                <textarea name="billing_notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('billing_notes', $tenant->billing_notes) }}</textarea>
            </div>
            <button type="submit" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:text-indigo-300">{{ __('Save profile') }}</button>
        </div>
    </form>

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Active subscriptions') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($billableSubscriptions as $sub)
                    <li class="px-4 py-3 text-sm">
                        <span class="font-medium">{{ $sub->project?->name }}</span>
                        <span class="text-gray-500"> — {{ $sub->package_name }}</span>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ strtoupper($sub->currency ?? $currency) }} {{ number_format((float) ($sub->monthly_fee ?? 0), 2) }}/{{ $sub->billing_cycle }}
                            @if ((float) ($sub->setup_fee ?? 0) > 0)
                                · {{ __('Setup') }} {{ number_format((float) $sub->setup_fee, 2) }}
                                @if ($draftGenerator->setupFeeAlreadyInvoiced($tenant, $sub))
                                    <span class="text-emerald-600">({{ __('invoiced') }})</span>
                                @endif
                            @endif
                        </p>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">{{ __('No billable subscriptions. Suspended or disabled products are excluded.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Billable modules') }}</h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                @php $hasModules = false; @endphp
                @foreach ($billableSubscriptions as $sub)
                    @foreach ($sub->moduleSubscriptions as $mod)
                        @if ($mod->enabled && $mod->subscribed)
                            @php
                                $hasModules = true;
                                $price = $mod->monthly_price_override ?? $mod->projectModule?->monthly_price ?? 0;
                            @endphp
                            @if ((float) $price > 0)
                                <li class="px-4 py-2 text-sm">{{ $sub->project?->name }} — {{ $mod->projectModule?->name }}: {{ number_format((float) $price, 2) }}</li>
                            @endif
                        @endif
                    @endforeach
                @endforeach
                @unless ($hasModules)
                    <li class="px-4 py-6 text-sm text-gray-500">{{ __('No subscribed modules with pricing.') }}</li>
                @endunless
            </ul>
        </div>
    </div>
</div>

<div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Invoices') }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2">{{ __('Invoice') }}</th>
                    <th class="px-4 py-2">{{ __('Issue') }}</th>
                    <th class="px-4 py-2">{{ __('Due') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('Balance') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($tenant->invoices as $inv)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('invoices.show', $inv) }}" class="font-mono text-xs text-indigo-600 hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-4 py-2">{{ optional($inv->issue_date ?? $inv->issued_at)->toFormattedDateString() ?? '—' }}</td>
                        <td class="px-4 py-2">{{ optional($inv->due_date)->toFormattedDateString() ?? '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $inv->currency ?? $currency }} {{ number_format($inv->invoiceTotal(), 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($inv->balanceDue(), 2) }}</td>
                        <td class="px-4 py-2 capitalize">{{ $inv->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No invoices yet. Generate a draft when billable items exist.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
