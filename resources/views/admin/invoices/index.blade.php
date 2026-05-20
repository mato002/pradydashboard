@php
    $trendMax = max(collect($invoiceTrend)->max(fn ($p) => max($p['issued'], $p['paid'])) ?? 0, 1);
    $revenueMax = max(collect($revenueSeries)->max('value') ?? 0, 1);
@endphp

<x-dashboard-layout :heading="__('Financial Operations')" :subheading="__('Billing command center')">
    <div x-data="{ toast: @js(session('status')) }" x-init="if (toast) setTimeout(() => toast = null, 5000)" class="space-y-5">
        <div x-show="toast" x-transition class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-emerald-500/30 bg-emerald-950/90 px-4 py-3 text-sm text-emerald-100 shadow-2xl" x-cloak>
            <span x-text="toast"></span>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">{{ __('Prady Dashboard') }}</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Financial Operations Command Center') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('Subscriptions, invoicing, collections, documents, and automation — real data only.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('invoices.generate') }}">@csrf
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg">{{ __('Run billing cycle') }}</button>
                </form>
                <form method="POST" action="{{ route('invoices.reminders') }}">@csrf
                    <button type="submit" class="rounded-xl border px-4 py-2.5 text-sm font-semibold">{{ __('Collections run') }}</button>
                </form>
            </div>
        </div>

        @include('admin.invoices.partials.nav')

        @if ($tab === 'overview')
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6">
                @foreach ([
                    [__('Total invoiced'), $kpis['totalInvoiced'], 'indigo'],
                    [__('Paid invoices'), $kpis['paid'], 'emerald'],
                    [__('Outstanding'), $kpis['outstanding'], 'amber'],
                    [__('Overdue'), $kpis['overdue'], 'rose'],
                    [__('Month revenue'), $kpis['monthRevenue'], 'violet'],
                    [__('Failed collections'), $kpis['failedCollections'], 'sky'],
                    [__('MRR'), $kpis['mrr'], 'indigo'],
                    [__('ARR'), $kpis['arr'], 'violet'],
                    [__('Collection efficiency'), $kpis['collectionRate'].'%', 'emerald'],
                    [__('Revenue forecast'), $kpis['revenueForecast'], 'amber'],
                    [__('Grace exposure'), $kpis['graceExposure'], 'amber'],
                    [__('Suspension risk'), $kpis['suspensionRisk'].' '.__('tenants'), 'rose'],
                ] as [$title, $value, $tone])
                    <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-[10px] font-semibold uppercase text-slate-500">{{ $title }}</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-5 lg:grid-cols-12">
                <div class="lg:col-span-8 space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-sm font-semibold">{{ __('Invoice trends') }}</h3>
                            <div class="mt-3 flex h-32 items-end gap-1">
                                @foreach ($invoiceTrend as $point)
                                    @php $h = max(6, (int) round(($point['issued'] / $trendMax) * 100)); @endphp
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t bg-indigo-500" style="height:{{ $h }}px"></div>
                                        <span class="text-[9px] text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-sm font-semibold">{{ __('Revenue collected') }}</h3>
                            <div class="mt-3 flex h-32 items-end gap-1">
                                @foreach ($revenueSeries as $point)
                                    @php $h = max(6, (int) round(($point['value'] / $revenueMax) * 100)); @endphp
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t bg-violet-500" style="height:{{ $h }}px"></div>
                                        <span class="text-[9px] text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @include('admin.invoices.partials.register-table')
                </div>
                <div class="lg:col-span-4 space-y-4">
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Top debtors') }}</h3>
                        <ul class="mt-2 space-y-2 text-sm">
                            @forelse ($topDebtors as $row)
                                <li class="flex justify-between"><span>{{ $row['tenant'] }}</span><span class="font-mono text-rose-600">{{ $row['balance'] }}</span></li>
                            @empty
                                <li class="text-slate-500">{{ __('No outstanding balances.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Upcoming renewals') }}</h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            @forelse ($upcomingRenewals as $row)
                                <li><span class="font-medium">{{ $row['tenant'] }}</span> · {{ $row['renewal_date'] }} · {{ $row['monthly_fee'] }}</li>
                            @empty
                                <li class="text-slate-500">{{ __('No renewals in the next 30 days.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Failed deliveries') }}</h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            @forelse ($failedDeliveries as $inv)
                                <li><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">{{ $inv->invoice_number }}</a> — {{ $inv->tenant?->company_name }}</li>
                            @empty
                                <li class="text-slate-500">{{ __('All deliveries OK.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Expiring subscriptions') }}</h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            @forelse ($expiringSubscriptions as $sub)
                                <li>{{ $sub->tenant?->company_name }} · {{ $sub->project?->name }} · {{ $sub->renewal_date?->format('M j') ?? $sub->license_status }}</li>
                            @empty
                                <li class="text-slate-500">{{ __('No expiring subscriptions.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @elseif ($tab === 'recurring')
            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="prady-table">
                    <thead><tr>
                        <th>{{ __('Schedule') }}</th><th>{{ __('Tenant') }}</th><th>{{ __('Amount') }}</th>
                        <th>{{ __('Frequency') }}</th><th>{{ __('Next run') }}</th><th>{{ __('Enabled') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td class="font-semibold">{{ $schedule->name }}</td>
                                <td>{{ $schedule->tenant?->company_name }}</td>
                                <td class="font-mono text-xs">{{ \App\Models\TenantInvoice::formatMoney($schedule->totalWithTax()) }}</td>
                                <td>{{ $schedule->frequencyLabel() }}</td>
                                <td>{{ $schedule->next_run_at?->format('M j, H:i') ?? '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('invoices.schedules.toggle', $schedule) }}">@csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold {{ $schedule->enabled ? 'text-emerald-600' : 'text-slate-400' }}">{{ $schedule->enabled ? __('On') : __('Off') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No recurring schedules.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif ($tab === 'collections')
            <div class="grid gap-5 lg:grid-cols-12">
                <div class="lg:col-span-7">@include('admin.invoices.partials.register-table')</div>
                <div class="lg:col-span-5 space-y-4">
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Aging buckets') }}</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach ($agingBuckets as $bucket)
                                <li>
                                    <div class="flex justify-between text-xs"><span>{{ $bucket['label'] }} ({{ $bucket['count'] }})</span><span>{{ \App\Models\TenantInvoice::formatMoney($bucket['amount']) }}</span></div>
                                    <div class="mt-1 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-500" style="width:{{ max(2,$bucket['pct']) }}%"></div></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold">{{ __('Collection notes') }}</h3>
                        <ul class="mt-2 max-h-64 space-y-2 overflow-y-auto text-xs">
                            @forelse ($collectionNotes as $note)
                                <li class="border-b border-slate-100 pb-2 dark:border-slate-800">
                                    <span class="font-semibold capitalize">{{ str_replace('_',' ',$note->note_type) }}</span>
                                    — {{ $note->invoice?->invoice_number }}
                                    <p class="text-slate-500">{{ $note->body }}</p>
                                </li>
                            @empty
                                <li class="text-slate-500">{{ __('No collection activity yet.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @elseif ($tab === 'templates')
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($templates as $template)
                    <form method="POST" action="{{ route('invoices.templates.update', $template) }}" class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        @csrf @method('PATCH')
                        <p class="text-xs uppercase text-slate-500">{{ $template->type }} · {{ $template->style }}</p>
                        <input name="name" value="{{ $template->name }}" class="mt-1 w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <input name="primary_color" value="{{ $template->brandingValue('primary_color', '#4f46e5') }}" placeholder="{{ __('Primary') }}" class="rounded text-xs">
                            <input name="accent_color" value="{{ $template->brandingValue('accent_color', '#f59e0b') }}" placeholder="{{ __('Accent') }}" class="rounded text-xs">
                        </div>
                        <textarea name="footer_text" rows="2" class="mt-2 w-full rounded text-xs" placeholder="{{ __('Footer') }}">{{ $template->brandingValue('footer_text') }}</textarea>
                        <label class="mt-2 flex items-center gap-2 text-xs"><input type="checkbox" name="show_qr" value="1" @checked($template->brandingValue('show_qr'))> {{ __('QR code') }}</label>
                        <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Save template') }}</button>
                    </form>
                @endforeach
            </div>
        @elseif ($tab === 'automation')
            <form method="POST" action="{{ route('invoices.automation.update') }}" class="max-w-xl rounded-2xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                @csrf @method('PUT')
                <h3 class="text-sm font-semibold">{{ __('Billing automation rules') }}</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        'reminder_after_days' => __('Reminder after (days)'),
                        'penalty_after_days' => __('Penalty after (days)'),
                        'suspension_after_days' => __('Suspension after (days)'),
                        'grace_period_days' => __('Grace period (days)'),
                        'penalty_percent' => __('Penalty %'),
                        'vat_percent' => __('VAT %'),
                    ] as $field => $label)
                        <div>
                            <label class="text-xs text-slate-500">{{ $label }}</label>
                            <input type="number" step="0.01" name="{{ $field }}" value="{{ $automationRules->$field }}" class="mt-1 w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    @foreach (['recurring_enabled','auto_send_invoices','auto_send_receipts','auto_generate_pdf'] as $flag)
                        <label class="flex items-center gap-2"><input type="checkbox" name="{{ $flag }}" value="1" @checked($automationRules->$flag)> {{ str_replace('_',' ',ucfirst($flag)) }}</label>
                    @endforeach
                </div>
                <button type="submit" class="mt-4 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save rules') }}</button>
            </form>
        @elseif ($tab === 'activity')
            <x-admin.activity-feed :logs="$activityLogs" />
        @else
            @include('admin.invoices.partials.register-table')
        @endif
    </div>
</x-dashboard-layout>
