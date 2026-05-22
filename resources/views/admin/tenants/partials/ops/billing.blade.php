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

<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 dark:border-emerald-900 dark:bg-emerald-950/20">
    <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ __('Record payment') }}</h3>
    <p class="mt-1 text-xs text-slate-600">{{ __('Record against a specific invoice from the invoice page, or save an unreconciled payment to the Payment Inbox.') }}</p>
    <div class="mt-3">
        @include('admin.invoices.partials.record-payment-form', [
            'formAction' => route('invoices.payments.record'),
            'defaultTenantId' => $tenant->id,
            'filterTenants' => collect([$tenant]),
            'paymentSources' => \App\Support\Billing\PaymentSource::all(),
            'compact' => true,
        ])
    </div>
    <a href="{{ route('invoices.index', ['tab' => 'payments', 'tenant_id' => $tenant->id]) }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600">{{ __('Open Payment Inbox for this tenant →') }}</a>
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

@if (! empty($tenantCollections))
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-100">{{ __('Collections overview') }}</h3>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500">{{ __('Unpaid invoices') }}</dt><dd class="font-semibold">{{ $tenantCollections['unpaid_invoices']->count() }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Overdue') }}</dt><dd class="font-semibold text-rose-700">{{ $tenantCollections['overdue_invoices']->count() }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Open promises') }}</dt><dd class="font-semibold">{{ $tenantCollections['promises']->count() }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Next follow-up') }}</dt><dd class="font-semibold">{{ optional($tenantCollections['next_follow_up'])->format('M j, Y') ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold">{{ __('Open collection notes') }}</h3>
            <ul class="mt-2 max-h-40 space-y-2 overflow-y-auto text-xs">
                @forelse ($tenantCollections['open_collection_notes'] ?? $tenantCollections['collection_notes'] as $note)
                    <li>
                        <a href="{{ route('invoices.show', $note->invoice) }}" class="font-semibold text-indigo-600">{{ $note->invoice?->invoice_number }}</a>
                        <span class="text-slate-500 capitalize"> · {{ str_replace('_', ' ', $note->outcome ?? $note->note_type) }}</span>
                        <p>{{ Str::limit($note->displayText(), 70) }}</p>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No collection notes.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
    @if ($tenantCollections['unpaid_invoices']->isNotEmpty())
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold">{{ __('Unpaid invoices') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($tenantCollections['unpaid_invoices'] as $inv)
                    <li class="flex justify-between gap-2">
                        <a href="{{ route('invoices.show', $inv) }}#collections" class="font-mono text-indigo-600">{{ $inv->invoice_number }}</a>
                        <span class="font-mono text-xs">{{ $inv->formattedBalance() }} · {{ $inv->due_date?->format('M j') ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @if ($tenantCollections['overdue_invoices']->isNotEmpty())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/30 p-4 dark:border-amber-900 dark:bg-amber-950/20">
            <h3 class="text-sm font-semibold">{{ __('Overdue invoices') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($tenantCollections['overdue_invoices'] as $inv)
                    <li class="flex justify-between">
                        <a href="{{ route('invoices.show', $inv) }}" class="font-mono text-indigo-600">{{ $inv->invoice_number }}</a>
                        <span class="font-mono">{{ $inv->formattedBalance() }} · {{ $inv->due_date?->format('M j') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @if ($tenantCollections['promises']->isNotEmpty())
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 dark:border-emerald-900">
            <h3 class="text-sm font-semibold">{{ __('Promise to pay') }}</h3>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($tenantCollections['promises'] as $note)
                    <li>
                        <a href="{{ route('invoices.show', $note->invoice) }}" class="text-indigo-600">{{ $note->invoice?->invoice_number }}</a>
                        — {{ $note->promise_to_pay_date?->format('M j, Y') }}
                        @if ($note->promised_amount)
                            ({{ \App\Models\TenantInvoice::formatMoney((float) $note->promised_amount, $note->invoice?->currency) }})
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif

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
