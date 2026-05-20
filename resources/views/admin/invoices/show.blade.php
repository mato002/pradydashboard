@php
    $currency = $invoice->currency ?? 'KES';
@endphp

<x-dashboard-layout :heading="$invoice->invoice_number" :subheading="__('Invoice detail')">
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('tenants.show', ['tenant' => $invoice->tenant, 'tab' => 'billing']) }}" class="text-sm text-indigo-600 hover:underline">
                    {{ $invoice->tenant?->company_name }}
                </a>
                <p class="mt-1 text-xs text-gray-500">
                    {{ __('Status') }}: <span class="font-semibold capitalize">{{ $invoice->statusLabel() }}</span>
                    · {{ __('Due') }} {{ optional($invoice->due_date)->toFormattedDateString() ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.preview', $invoice) }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Preview') }}</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('PDF') }}</a>
                <form method="post" action="{{ route('invoices.email', $invoice) }}" class="inline">@csrf
                    <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Email') }}</button>
                </form>
                @if ($invoice->document_type === 'quotation' && $invoice->approval_status !== 'approved')
                    <form method="post" action="{{ route('invoices.quotations.approve', $invoice) }}">@csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Approve') }}</button>
                    </form>
                @endif
                @if ($invoice->document_type === 'quotation' && $invoice->approval_status === 'approved' && ! $invoice->converted_invoice_id)
                    <form method="post" action="{{ route('invoices.quotations.convert', $invoice) }}">@csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Convert to invoice') }}</button>
                    </form>
                @endif
                @if ($invoice->status === 'draft')
                    <form method="post" action="{{ route('invoices.mark-sent', $invoice) }}">@csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Mark as sent') }}</button>
                    </form>
                @endif
                @if (! in_array($invoice->status, ['cancelled', 'void', 'paid']))
                    <form method="post" action="{{ route('invoices.cancel', $invoice) }}">@csrf
                        <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Cancel') }}</button>
                    </form>
                @endif
                @if ($invoice->balanceDue() <= 0.009 && $invoice->status !== 'paid')
                    <form method="post" action="{{ route('invoices.mark-paid', $invoice) }}">@csrf
                        <button type="submit" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700">{{ __('Mark paid') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h3 class="text-sm font-semibold">{{ __('Line items') }}</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-950">
                        <tr>
                            <th class="px-4 py-2">{{ __('Description') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Qty') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Unit') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Tax') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($invoice->lineItems as $line)
                            <tr>
                                <td class="px-4 py-2">
                                    <span class="text-xs uppercase text-gray-400">{{ $line->item_type }}</span>
                                    <p>{{ $line->description }}</p>
                                </td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $line->quantity, 2) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $line->unit_price, 2) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $line->tax_amount, 2) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums font-medium">{{ number_format((float) $line->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No line items.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <dl class="border-t border-gray-200 px-4 py-4 text-sm dark:border-gray-800 sm:grid sm:grid-cols-2 sm:gap-2">
                    <div class="flex justify-between sm:block"><dt class="text-gray-500">{{ __('Subtotal') }}</dt><dd class="font-medium tabular-nums">{{ $currency }} {{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between sm:block"><dt class="text-gray-500">{{ __('Tax') }}</dt><dd class="font-medium tabular-nums">{{ $currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</dd></div>
                    <div class="flex justify-between sm:block sm:col-span-2 border-t border-dashed pt-2 dark:border-gray-700">
                        <dt class="font-semibold">{{ __('Total') }}</dt>
                        <dd class="text-lg font-semibold tabular-nums">{{ $currency }} {{ number_format($invoice->invoiceTotal(), 2) }}</dd>
                    </div>
                    <div class="flex justify-between sm:block"><dt class="text-gray-500">{{ __('Paid') }}</dt><dd class="tabular-nums">{{ $currency }} {{ number_format((float) $invoice->amount_paid, 2) }}</dd></div>
                    <div class="flex justify-between sm:block"><dt class="text-gray-500">{{ __('Balance') }}</dt><dd class="font-semibold tabular-nums">{{ $currency }} {{ number_format($invoice->balanceDue(), 2) }}</dd></div>
                </dl>
            </div>

            <div class="space-y-4">
                @if ($invoice->projectSubscription)
                    <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Subscription') }}</p>
                        <p class="mt-1 font-semibold">{{ $invoice->projectSubscription->project?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->projectSubscription->package_name }}</p>
                    </div>
                @endif

                @if ($billingSettings->paymentInstructions())
                    <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Payment instructions') }}</p>
                        <p class="mt-2 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $billingSettings->paymentInstructions() }}</p>
                    </div>
                @endif

                @if ($billingSettings->invoiceFooterNotes())
                    <p class="text-xs text-gray-500 whitespace-pre-line">{{ $billingSettings->invoiceFooterNotes() }}</p>
                @endif

                @if (! in_array($invoice->status, ['cancelled', 'void', 'paid']))
                    <form method="post" action="{{ route('invoices.payments.store', $invoice) }}" class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        @csrf
                        <p class="text-sm font-semibold">{{ __('Record payment') }}</p>
                        <div class="mt-3 space-y-2 text-sm">
                            <input type="number" step="0.01" name="amount" required placeholder="{{ __('Amount') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            <input name="method" required placeholder="{{ __('Method') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            <input name="reference" placeholder="{{ __('Reference') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            <textarea name="notes" rows="2" placeholder="{{ __('Notes') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white">{{ __('Record payment') }}</button>
                        </div>
                    </form>
                @endif

                @if ($invoice->payments->isNotEmpty())
                    <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                        <p class="text-xs font-medium uppercase text-gray-500">{{ __('Payments') }}</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($invoice->payments as $pay)
                                <li class="text-xs">
                                    {{ optional($pay->paid_at)->toFormattedDateString() }} — {{ $currency }} {{ number_format((float) $pay->amount, 2) }}
                                    <span class="text-gray-500">({{ $pay->method }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <x-admin.activity-feed :logs="$activityLogs" class="mt-6" />
    </div>
</x-dashboard-layout>
