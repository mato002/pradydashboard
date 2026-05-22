@php
    $kpis = $paymentKpis ?? [];
    $inboxMeta = $paymentInboxMeta ?? [];
@endphp

<div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-amber-600">{{ __('Unreconciled') }}</p>
        <p class="mt-1 text-lg font-semibold tabular-nums">{{ $kpis['unreconciled_count'] ?? 0 }}</p>
        <p class="text-xs text-slate-500">{{ \App\Models\TenantInvoice::formatMoney($kpis['unreconciled_amount'] ?? 0) }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-emerald-600">{{ __('Matched today') }}</p>
        <p class="mt-1 text-lg font-semibold">{{ $kpis['matched_today'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-rose-600">{{ __('Duplicates') }}</p>
        <p class="mt-1 text-lg font-semibold">{{ $kpis['duplicates'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Ignored') }}</p>
        <p class="mt-1 text-lg font-semibold">{{ $kpis['ignored'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-indigo-600">{{ __('This month') }}</p>
        <p class="mt-1 text-lg font-semibold">{{ $kpis['payments_this_month'] ?? 0 }}</p>
        <p class="text-xs text-slate-500">{{ \App\Models\TenantInvoice::formatMoney($kpis['month_collected'] ?? 0) }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Avg reconcile') }}</p>
        <p class="mt-1 text-lg font-semibold">{{ $kpis['avg_reconciliation_hours'] ?? 0 }}h</p>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-12">
    <div class="lg:col-span-4">
        @include('admin.invoices.partials.record-payment-form', [
            'filterTenants' => $filterTenants,
            'paymentSources' => $paymentSources,
        ])
    </div>
    <div class="lg:col-span-8">
        <form method="get" action="{{ route('invoices.index') }}" class="mb-4 space-y-3 rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
            <input type="hidden" name="tab" value="payments">
            <div class="flex flex-wrap gap-1">
                @php
                    $statusChips = [
                        '' => __('All'),
                        'unreconciled' => __('Unreconciled'),
                        'matched' => __('Matched'),
                        'partially_matched' => __('Partial'),
                        'duplicate' => __('Duplicate'),
                        'ignored' => __('Ignored'),
                    ];
                @endphp
                @foreach ($statusChips as $value => $label)
                    @php
                        $chipQuery = array_merge(request()->except('page', 'reconciliation_status'), ['tab' => 'payments']);
                        if ($value !== '') {
                            $chipQuery['reconciliation_status'] = $value;
                        }
                    @endphp
                    <a href="{{ route('invoices.index', $chipQuery) }}"
                       class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ request('reconciliation_status', '') === $value ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Source') }}</label>
                    <select name="source" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                        <option value="">{{ __('All sources') }}</option>
                        @foreach ($paymentSources as $src)
                            <option value="{{ $src }}" @selected(request('source') === $src)>{{ \App\Support\Billing\PaymentSource::label($src) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Tenant') }}</label>
                    <select name="tenant_id" class="mt-0.5 block min-w-[140px] rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                        <option value="">{{ __('All tenants') }}</option>
                        @foreach ($filterTenants as $t)
                            <option value="{{ $t->id }}" @selected((string) request('tenant_id') === (string) $t->id)>{{ $t->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('From') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('To') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
                <a href="{{ route('invoices.index', ['tab' => 'payments']) }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-700">{{ __('Reset') }}</a>
            </div>
        </form>

        <div class="space-y-3">
            @forelse ($paymentInbox as $pay)
                @php
                    $meta = $inboxMeta[$pay->id] ?? ['suggestions' => [], 'duplicate' => null];
                    $duplicateOf = $meta['duplicate'] ?? null;
                    $suggestions = $meta['suggestions'] ?? [];
                    $canMatch = $pay->reconciliation_status === 'unreconciled';
                    $canSplit = $canMatch || ($pay->reconciliation_status === 'partially_matched' && $pay->remainingToAllocate() > 0.009);
                @endphp
                <article id="payment-{{ $pay->id }}" class="rounded-2xl border bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b px-4 py-3 dark:border-slate-800">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-semibold">{{ $pay->formattedAmount() }}</span>
                                <x-ui.status-badge :variant="$pay->reconciliationVariant()">{{ $pay->reconciliationLabel() }}</x-ui.status-badge>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $pay->sourceLabel() }}</span>
                                @if ($duplicateOf && $pay->reconciliation_status !== 'duplicate')
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200" title="{{ __('Matches payment :ref', ['ref' => $duplicateOf->displayId()]) }}">
                                        {{ __('Possible duplicate') }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ optional($pay->paid_at)->format('M j, Y g:i A') ?? '—' }}
                                · {{ __('Ref') }}: <span class="font-mono">{{ $pay->reference ?? '—' }}</span>
                                @if ($pay->bank_source)
                                    · {{ $pay->bank_source }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if ($pay->isReconciled())
                                <form method="post" action="{{ route('invoices.payments.reverse', $pay) }}">@csrf
                                    <button type="submit" class="font-semibold text-rose-600 hover:underline">{{ __('Reverse') }}</button>
                                </form>
                            @endif
                            @if ($duplicateOf && $pay->reconciliation_status !== 'duplicate')
                                <form method="post" action="{{ route('invoices.payments.duplicate', $pay) }}">@csrf
                                    <button type="submit" class="font-semibold text-amber-700 hover:underline">{{ __('Mark duplicate') }}</button>
                                </form>
                            @elseif ($pay->reconciliation_status !== 'duplicate')
                                <form method="post" action="{{ route('invoices.payments.duplicate', $pay) }}">@csrf
                                    <button type="submit" class="font-semibold text-amber-600 hover:underline">{{ __('Flag duplicate') }}</button>
                                </form>
                            @endif
                            @if ($pay->reconciliation_status !== 'ignored')
                                <form method="post" action="{{ route('invoices.payments.ignore', $pay) }}">@csrf
                                    <button type="submit" class="font-semibold text-slate-500 hover:underline">{{ __('Ignore') }}</button>
                                </form>
                            @endif
                            @if ($canSplit)
                                <button type="button" class="font-semibold text-indigo-600 hover:underline" onclick="document.getElementById('split-dialog-{{ $pay->id }}')?.showModal()">
                                    {{ __('Split') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4 px-4 py-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Payer') }}</p>
                            <p class="mt-0.5 text-sm font-medium">{{ $pay->payer_name ?? $pay->tenant?->company_name ?? '—' }}</p>
                            @if ($pay->payer_phone)
                                <p class="text-xs text-slate-500">{{ $pay->payer_phone }}</p>
                            @endif
                            @if ($pay->payer_email)
                                <p class="text-xs text-slate-500">{{ $pay->payer_email }}</p>
                            @endif
                            @if ($pay->tenant && $pay->payer_name)
                                <p class="text-xs text-indigo-600">{{ $pay->tenant->company_name }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Details') }}</p>
                            @if ($pay->narration)
                                <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">{{ $pay->narration }}</p>
                            @endif
                            @if ($pay->notes)
                                <p class="mt-1 text-xs italic text-slate-500">{{ Str::limit($pay->notes, 120) }}</p>
                            @endif
                            @if ((float) $pay->unapplied_amount > 0)
                                <p class="mt-1 text-xs font-semibold text-amber-600">{{ __('Unapplied credit') }}: {{ \App\Models\TenantInvoice::formatMoney((float) $pay->unapplied_amount, $pay->currency) }}</p>
                            @endif
                            @if ($pay->allocations->isNotEmpty())
                                <ul class="mt-1 space-y-0.5 text-xs text-slate-500">
                                    @foreach ($pay->allocations as $alloc)
                                        <li>{{ $alloc->invoice?->invoice_number ?? '#' }} — {{ \App\Models\TenantInvoice::formatMoney((float) $alloc->amount, $pay->currency) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Method / gateway') }}</p>
                            <p class="mt-0.5 text-sm">{{ $pay->method ?? $pay->gatewayLabel() }}</p>
                            @if ($pay->matched_at)
                                <p class="mt-1 text-xs text-slate-500">{{ __('Matched') }} {{ $pay->matched_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($canMatch && count($suggestions) > 0)
                        <div class="border-t bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">{{ __('Suggested matches') }}</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($suggestions as $sug)
                                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-indigo-100 bg-white px-3 py-2 dark:border-indigo-900/40 dark:bg-slate-900">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('invoices.show', $sug['invoice_id']) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:underline">{{ $sug['invoice_number'] }}</a>
                                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-bold text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200">{{ $sug['score'] }} {{ __('pts') }}</span>
                                            </div>
                                            <p class="text-xs text-slate-600">{{ $sug['tenant'] }} · {{ __('Balance') }} {{ $sug['balance'] }} · {{ __('Due') }} {{ $sug['due_date'] }}</p>
                                            @if (! empty($sug['reasons']))
                                                <p class="mt-0.5 text-[10px] text-slate-500">{{ implode(' · ', $sug['reasons']) }}</p>
                                            @endif
                                        </div>
                                        <div class="flex shrink-0 flex-wrap items-center gap-1">
                                            @php
                                                $fullPayAmount = min((float) $pay->remainingToAllocate(), (float) $sug['balance_raw']);
                                            @endphp
                                            <form method="post" action="{{ route('invoices.payments.match', $pay) }}" class="inline">@csrf
                                                <input type="hidden" name="invoice_id" value="{{ $sug['invoice_id'] }}">
                                                @if ($sug['is_partial'])
                                                    <input type="hidden" name="amount" value="{{ $fullPayAmount }}">
                                                @endif
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white">
                                                    {{ $sug['is_partial'] ? __('Match (pay balance)') : __('Match') }}
                                                </button>
                                            </form>
                                            @if ($sug['is_partial'])
                                                <form method="post" action="{{ route('invoices.payments.match', $pay) }}" class="inline flex items-center gap-1">@csrf
                                                    <input type="hidden" name="invoice_id" value="{{ $sug['invoice_id'] }}">
                                                    <input type="number" step="0.01" name="amount" value="{{ $sug['suggested_amount'] }}" class="w-20 rounded border-slate-300 text-[11px] dark:bg-slate-950" title="{{ __('Amount to apply') }}">
                                                    <button type="submit" class="rounded-lg border border-amber-500 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:text-amber-300">
                                                        {{ __('Partial match') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($canMatch)
                        <div class="border-t px-4 py-2 text-xs text-slate-500 dark:border-slate-800">
                            {{ __('No automatic suggestions — use manual match below or split.') }}
                            <form method="post" action="{{ route('invoices.payments.match', $pay) }}" class="mt-2 flex flex-wrap gap-2">
                                @csrf
                                <input type="number" name="invoice_id" placeholder="{{ __('Invoice ID') }}" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950" required>
                                <input type="number" step="0.01" name="amount" placeholder="{{ __('Amount (optional)') }}" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950">
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">{{ __('Manual match') }}</button>
                            </form>
                        </div>
                    @endif

                    @if ($canSplit)
                        <dialog id="split-dialog-{{ $pay->id }}" class="w-full max-w-lg rounded-2xl border bg-white p-5 shadow-xl backdrop:bg-black/50 dark:border-slate-700 dark:bg-slate-900">
                            <form method="post" action="{{ route('invoices.payments.split', $pay) }}" data-split-form data-split-max="{{ (float) $pay->remainingToAllocate() }}" onsubmit="return validateSplitForm(this)">
                                @csrf
                                <h3 class="text-sm font-semibold">{{ __('Split payment') }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Payment') }} {{ $pay->displayId() }} · {{ __('Max to allocate') }} <span class="font-mono font-semibold">{{ number_format((float) $pay->remainingToAllocate(), 2) }}</span></p>
                                <div class="mt-4 space-y-2">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="flex gap-2">
                                            <input type="number" name="allocations[{{ $i }}][invoice_id]" placeholder="{{ __('Invoice ID') }}" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950">
                                            <input type="number" step="0.01" name="allocations[{{ $i }}][amount]" placeholder="{{ __('Amount') }}" class="flex-1 rounded border-slate-300 text-xs dark:bg-slate-950" data-split-amount-input oninput="updateSplitRemaining(this.closest('[data-split-form]'))">
                                        </div>
                                    @endfor
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ __('Remaining unallocated') }}: <span class="font-mono font-semibold text-emerald-600" data-split-remaining>{{ number_format((float) $pay->remainingToAllocate(), 2) }}</span></p>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" class="rounded-lg border px-3 py-1.5 text-xs" onclick="this.closest('dialog')?.close()">{{ __('Cancel') }}</button>
                                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Apply split') }}</button>
                                </div>
                            </form>
                        </dialog>
                    @endif
                </article>
            @empty
                <p class="rounded-2xl border border-dashed px-6 py-12 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('No payments in inbox.') }}</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $paymentInbox->links() }}</div>
    </div>
</div>

<script>
    function updateSplitRemaining(formEl) {
        if (!formEl) return;
        const maxAmount = parseFloat(formEl.dataset.splitMax) || 0;
        let sum = 0;
        formEl.querySelectorAll('[data-split-amount-input]').forEach((input) => {
            const row = input.closest('.flex');
            const invId = row?.querySelector('[name*="invoice_id"]')?.value;
            const v = parseFloat(input.value);
            if (invId && !isNaN(v) && v > 0) sum += v;
        });
        const remaining = Math.max(0, maxAmount - sum);
        const el = formEl.querySelector('[data-split-remaining]');
        if (el) {
            el.textContent = remaining.toFixed(2);
            el.classList.toggle('text-rose-600', sum > maxAmount + 0.009);
            el.classList.toggle('text-emerald-600', sum <= maxAmount + 0.009);
        }
    }
    function validateSplitForm(formEl) {
        const maxAmount = parseFloat(formEl.dataset.splitMax) || 0;
        let sum = 0;
        let hasLine = false;
        formEl.querySelectorAll('[data-split-amount-input]').forEach((input) => {
            const row = input.closest('.flex');
            const invId = row?.querySelector('[name*="invoice_id"]')?.value;
            const amt = parseFloat(input.value);
            if (invId && !isNaN(amt) && amt > 0) {
                hasLine = true;
                sum += amt;
            }
        });
        if (!hasLine) {
            alert(@json(__('Add at least one invoice allocation.')));
            return false;
        }
        if (sum > maxAmount + 0.009) {
            alert(@json(__('Split total exceeds available payment amount.')));
            return false;
        }
        return true;
    }
</script>
