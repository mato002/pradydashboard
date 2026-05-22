@php
    $c = $collections ?? [];
@endphp

<div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-rose-600">{{ __('Overdue') }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $c['overdue']->count() ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-amber-600">{{ __('Due soon (7d)') }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $c['due_soon']->count() ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Unpaid sent') }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $c['unpaid_sent']->count() ?? 0 }}</p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-sky-600">{{ __('Partially paid') }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $c['partially_paid']->count() ?? 0 }}</p>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-12">
    <div class="lg:col-span-8 space-y-5">
        @foreach ([
            'overdue' => __('Overdue invoices'),
            'due_soon' => __('Due soon'),
            'unpaid_sent' => __('Unpaid sent'),
            'partially_paid' => __('Partially paid'),
        ] as $key => $title)
            <div class="rounded-2xl border bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b px-4 py-2 dark:border-slate-800">
                    <h3 class="text-sm font-semibold">{{ $title }} ({{ ($c[$key] ?? collect())->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-left uppercase text-slate-500 dark:bg-slate-950">
                            <tr>
                                <th class="px-3 py-2">{{ __('Invoice') }}</th>
                                <th class="px-3 py-2">{{ __('Client') }}</th>
                                <th class="px-3 py-2">{{ __('Due') }}</th>
                                @if ($key === 'overdue')
                                    <th class="px-3 py-2">{{ __('Days overdue') }}</th>
                                @endif
                                <th class="px-3 py-2 text-right">{{ __('Balance') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-slate-800">
                            @forelse ($c[$key] ?? [] as $inv)
                                <tr>
                                    <td class="px-3 py-2 font-mono"><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">{{ $inv->invoice_number }}</a></td>
                                    <td class="px-3 py-2">{{ $inv->tenant?->company_name ?? $inv->manual_client_name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $inv->due_date?->format('M j, Y') ?? '—' }}</td>
                                    @if ($key === 'overdue')
                                        <td class="px-3 py-2 tabular-nums text-rose-600">{{ $inv->due_date ? $inv->due_date->diffInDays(now()->startOfDay()) : '—' }}</td>
                                    @endif
                                    <td class="px-3 py-2 text-right font-mono">{{ $inv->formattedBalance() }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('invoices.show', $inv) }}#collections" class="font-semibold text-indigo-600 hover:underline">{{ __('Follow up') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $key === 'overdue' ? 6 : 5 }}" class="px-3 py-4 text-center text-slate-500">{{ __('None') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lg:col-span-4 space-y-4">
        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold">{{ __('Aging (days overdue)') }}</h3>
            <ul class="mt-3 space-y-2">
                @foreach ($c['aging_buckets'] ?? [] as $bucket)
                    <li>
                        <div class="flex justify-between text-xs"><span>{{ $bucket['label'] }} ({{ $bucket['count'] }})</span><span>{{ \App\Models\TenantInvoice::formatMoney($bucket['amount']) }}</span></div>
                        <div class="mt-1 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-rose-500" style="width:{{ max(2, $bucket['pct']) }}%"></div></div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold">{{ __('Top debtors') }}</h3>
            <ul class="mt-2 space-y-2 text-xs">
                @forelse ($c['top_debtors'] ?? [] as $debtor)
                    <li class="flex justify-between"><span>{{ $debtor['tenant'] }}</span><span class="font-mono">{{ $debtor['balance'] }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No outstanding balances') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold">{{ __('Promised payments') }}</h3>
            <ul class="mt-2 max-h-40 space-y-2 overflow-y-auto text-xs">
                @forelse ($c['promised_payments'] ?? [] as $note)
                    <li>
                        <a href="{{ route('invoices.show', $note->invoice) }}" class="font-semibold text-indigo-600">{{ $note->invoice?->invoice_number }}</a>
                        <p>{{ $note->promise_to_pay_date?->format('M j') }} · {{ $note->promised_amount ? \App\Models\TenantInvoice::formatMoney((float) $note->promised_amount, $note->invoice?->currency) : '—' }}</p>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No open promises') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold">{{ __('Overdue follow-ups') }}</h3>
            <ul class="mt-2 max-h-40 space-y-2 overflow-y-auto text-xs">
                @forelse ($c['overdue_follow_ups'] ?? [] as $note)
                    <li>
                        <a href="{{ route('invoices.show', $note->invoice) }}" class="text-indigo-600">{{ $note->invoice?->invoice_number }}</a>
                        <p class="text-rose-600">{{ __('Due') }} {{ $note->follow_up_date?->format('M j') }}</p>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('None overdue') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <h3 class="text-sm font-semibold text-rose-800 dark:text-rose-200">{{ __('Suspension candidates') }}</h3>
            <ul class="mt-2 space-y-2 text-xs">
                @forelse ($c['suspension_candidates'] ?? [] as $row)
                    <li>
                        @if ($row['tenant_id'])
                            <a href="{{ route('tenants.show', ['tenant' => $row['tenant_id'], 'tab' => 'billing']) }}" class="font-semibold text-rose-800 dark:text-rose-200">{{ $row['tenant'] }}</a>
                        @else
                            {{ $row['tenant'] }}
                        @endif
                        <p>{{ $row['invoice_count'] }} {{ __('invoices') }} · {{ \App\Models\TenantInvoice::formatMoney($row['balance']) }} · {{ $row['max_days_overdue'] }}d</p>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('None at threshold') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold">{{ __('Recent notes') }}</h3>
            <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-xs">
                @forelse ($collectionNotes as $note)
                    <li class="border-b pb-2 dark:border-slate-800">
                        <a href="{{ route('invoices.show', $note->invoice) }}" class="font-semibold text-indigo-600">{{ $note->invoice?->invoice_number }}</a>
                        <span class="capitalize text-slate-500"> · {{ str_replace('_', ' ', $note->outcome ?? $note->note_type) }}</span>
                        <p class="text-slate-600">{{ Str::limit($note->displayText(), 80) }}</p>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No collection activity yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
