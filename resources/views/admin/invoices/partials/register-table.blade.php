@php
    $trendMax = max(collect($invoiceTrend ?? [])->max(fn ($p) => max($p['issued'], $p['paid'])) ?? 0, 1);
    $revenueMax = max(collect($revenueSeries ?? [])->max('value') ?? 0, 1);
@endphp
<form method="get" action="{{ route('invoices.index') }}" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-800 dark:bg-slate-950/40">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Tenant') }}</label>
        <select name="tenant_id" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
            <option value="">{{ __('All') }}</option>
            @foreach ($filterTenants as $t)
                <option value="{{ $t->id }}" @selected(request('tenant_id') == $t->id)>{{ $t->company_name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('From') }}</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
    </div>
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500">{{ __('To') }}</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
    </div>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="overdue" value="1" @checked(request()->boolean('overdue'))> {{ __('Overdue') }}</label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="unpaid" value="1" @checked(request()->boolean('unpaid'))> {{ __('Unpaid') }}</label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="paid" value="1" @checked(request()->boolean('paid'))> {{ __('Paid') }}</label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="draft" value="1" @checked(request()->boolean('draft'))> {{ __('Draft') }}</label>
    <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white dark:bg-slate-200 dark:text-slate-900">{{ __('Filter') }}</button>
</form>

<x-ui.table-panel :title="__('Document register')">
    <table class="prady-table">
        <thead>
            <tr>
                <th>{{ __('No.') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
                <th class="text-right">{{ __('Paid') }}</th>
                <th class="text-right">{{ __('Balance') }}</th>
                <th>{{ __('Due') }}</th>
                <th>{{ __('Delivery') }}</th>
                <th>{{ __('By') }}</th>
                <th>{{ __('Aging') }}</th>
                <th class="text-right">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
            @forelse ($invoices as $invoice)
                <tr @class(['bg-rose-50/50 dark:bg-rose-950/20' => $invoice->status === 'overdue'])>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td class="text-sm">{{ $invoice->tenant?->company_name ?? '—' }}</td>
                    <td class="max-w-[100px] truncate text-xs">{{ $invoice->projectSubscription?->project?->name ?? $invoice->product_name ?? '—' }}</td>
                    <td class="text-xs">{{ $invoice->documentTypeLabel() }}</td>
                    <td><x-ui.status-badge :variant="$invoice->statusVariant()">{{ $invoice->statusLabel() }}</x-ui.status-badge></td>
                    <td class="text-right font-mono text-xs tabular-nums">{{ $invoice->formattedAmount() }}</td>
                    <td class="text-right font-mono text-xs tabular-nums">{{ \App\Models\TenantInvoice::formatMoney((float) $invoice->amount_paid, $invoice->currency) }}</td>
                    <td class="text-right font-mono text-xs tabular-nums">{{ $invoice->formattedBalance() }}</td>
                    <td class="text-xs">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                    <td class="text-xs">{{ $invoice->deliveryStatusLabel() }}</td>
                    <td class="max-w-[80px] truncate text-xs text-slate-500" title="{{ $invoice->generated_by }}">{{ $invoice->generated_by ?? '—' }}</td>
                    <td class="text-xs font-medium {{ $invoice->agingColor() }}">{{ $invoice->agingLabel() }}</td>
                    <td class="text-right">
                        <div class="inline-flex gap-1">
                            <a href="{{ route('invoices.preview', $invoice) }}" title="{{ __('Preview') }}" class="rounded p-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">👁</a>
                            <a href="{{ route('invoices.pdf', $invoice) }}" title="{{ __('PDF') }}" class="rounded p-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">📄</a>
                            <form method="post" action="{{ route('invoices.email', $invoice) }}" class="inline">@csrf
                                <button type="submit" title="{{ __('Email') }}" class="rounded p-1 text-slate-500 hover:bg-slate-100">✉</button>
                            </form>
                            <a href="{{ route('invoices.show', $invoice) }}" class="rounded p-1 text-indigo-600 text-xs font-semibold">{{ __('Open') }}</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="py-12 text-center text-sm text-slate-500">{{ __('No documents match your filters.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <x-slot name="footer">{{ $invoices->links() }}</x-slot>
</x-ui.table-panel>
