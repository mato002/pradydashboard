@php
    $formAction = $formAction ?? route('invoices.payments.record');
    $defaultTenantId = $defaultTenantId ?? '';
    $defaultInvoiceId = $defaultInvoiceId ?? '';
    $compact = $compact ?? false;
@endphp

<form method="post" action="{{ $formAction }}" class="{{ $compact ? 'space-y-2 text-sm' : 'rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900 space-y-3' }}">
    @csrf
    @if ($defaultInvoiceId)
        <input type="hidden" name="tenant_invoice_id" value="{{ $defaultInvoiceId }}">
    @endif
    @unless ($compact)
        <h3 class="text-sm font-semibold">{{ __('Record payment') }}</h3>
    @endunless
    <div class="{{ $compact ? 'space-y-2' : 'grid gap-3 sm:grid-cols-2 lg:grid-cols-3' }}">
        @if (empty($defaultInvoiceId))
            <div>
                <label class="text-xs text-slate-500">{{ __('Tenant (optional)') }}</label>
                <select name="tenant_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    <option value="">{{ __('— Unmatched —') }}</option>
                    @foreach ($filterTenants ?? [] as $t)
                        <option value="{{ $t->id }}" @selected($defaultTenantId == $t->id)>{{ $t->company_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="text-xs text-slate-500">{{ __('Payer name') }}</label>
            <input name="payer_name" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Amount') }} *</label>
            <input type="number" step="0.01" min="0.01" name="amount" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Payment date') }} *</label>
            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Source') }} *</label>
            <select name="source" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                @foreach ($paymentSources ?? \App\Support\Billing\PaymentSource::all() as $src)
                    <option value="{{ $src }}">{{ \App\Support\Billing\PaymentSource::label($src) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Reference / transaction code') }}</label>
            <input name="reference" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Bank / account / source') }}</label>
            <input name="bank_source" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Payer phone') }}</label>
            <input name="payer_phone" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Payer email') }}</label>
            <input type="email" name="payer_email" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs text-slate-500">{{ __('Narration / description') }}</label>
            <input name="narration" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
    </div>
    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Record payment') }}</button>
    @if (empty($defaultInvoiceId))
        <p class="text-[10px] text-slate-500">{{ __('Without an invoice, payment is saved as unreconciled in the Payment Inbox.') }}</p>
    @endif
</form>
