@php
    $openNotes = $invoice->collectionNotes()->open()->orderBy('follow_up_date')->get();
    $outcomes = \App\Support\Billing\CollectionNoteOutcome::labels();
    $statuses = [
        \App\Support\Billing\CollectionNoteStatus::OPEN => __('Open'),
        \App\Support\Billing\CollectionNoteStatus::COMPLETED => __('Completed'),
        \App\Support\Billing\CollectionNoteStatus::CANCELLED => __('Cancelled'),
    ];
@endphp

<div id="collections" class="rounded-2xl border border-amber-200/80 bg-amber-50/30 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Collections & follow-up') }}</h3>
    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
        {{ __('Balance') }}: <span class="font-mono font-semibold">{{ $invoice->formattedBalance() }}</span>
        @if ($invoice->due_date)
            · {{ __('Due') }} {{ $invoice->due_date->format('M j, Y') }}
            @if ($invoice->due_date->isPast())
                · <span class="font-semibold text-rose-600">{{ __(':days days overdue', ['days' => $invoice->due_date->diffInDays(now()->startOfDay())]) }}</span>
            @endif
        @endif
    </p>

    <details class="mt-3 group" open>
        <summary class="cursor-pointer text-xs font-semibold text-indigo-600">{{ __('Collection actions') }}</summary>
        <div class="mt-3 grid gap-4 lg:grid-cols-2">
            <form method="post" action="{{ route('invoices.collection-notes.store', $invoice) }}" class="space-y-2 rounded-lg border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <p class="text-xs font-semibold">{{ __('Add collection note') }}</p>
                <textarea name="note" rows="2" required class="w-full rounded border-slate-300 text-xs dark:bg-slate-950" placeholder="{{ __('Call log, email thread…') }}"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-slate-500">{{ __('Outcome') }}</label>
                        <select name="outcome" class="mt-0.5 w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                            <option value="">{{ __('—') }}</option>
                            @foreach ($outcomes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-500">{{ __('Status') }}</label>
                        <select name="status" class="mt-0.5 w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($value === \App\Support\Billing\CollectionNoteStatus::OPEN)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-slate-500">{{ __('Follow-up date') }}</label>
                        <input type="date" name="follow_up_date" class="mt-0.5 w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-500">{{ __('Promise to pay') }}</label>
                        <input type="date" name="promise_to_pay_date" class="mt-0.5 w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                    </div>
                </div>
                <input type="number" step="0.01" name="promised_amount" placeholder="{{ __('Promised amount') }}" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                <button type="submit" class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white">{{ __('Save note') }}</button>
            </form>

            <form method="post" action="{{ route('invoices.promise-to-pay', $invoice) }}" class="space-y-2 rounded-lg border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <p class="text-xs font-semibold">{{ __('Mark promise to pay') }}</p>
                <input type="date" name="promise_to_pay_date" required value="{{ old('promise_to_pay_date', now()->addDays(7)->toDateString()) }}" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                <input type="number" step="0.01" name="promised_amount" placeholder="{{ __('Amount') }}" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date', now()->addDays(7)->toDateString()) }}" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950" title="{{ __('Follow-up reminder date') }}">
                <textarea name="note" rows="1" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950" placeholder="{{ __('Notes') }}"></textarea>
                <button type="submit" class="rounded bg-emerald-600 px-2 py-1 text-xs font-semibold text-white">{{ __('Record promise') }}</button>
            </form>

            <form method="post" action="{{ route('invoices.disputed', $invoice) }}" class="space-y-2 rounded-lg border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <p class="text-xs font-semibold">{{ __('Mark disputed') }}</p>
                <textarea name="note" rows="2" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950" placeholder="{{ __('Dispute reason…') }}"></textarea>
                <input type="date" name="follow_up_date" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                <button type="submit" class="rounded border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-700">{{ __('Mark disputed') }}</button>
            </form>

            <form method="post" action="{{ route('invoices.escalate', $invoice) }}" class="space-y-2 rounded-lg border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <p class="text-xs font-semibold">{{ __('Escalate') }}</p>
                <textarea name="note" rows="2" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950"></textarea>
                <input type="date" name="follow_up_date" value="{{ now()->addDays(2)->toDateString() }}" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                <button type="submit" class="rounded bg-rose-600 px-2 py-1 text-xs font-semibold text-white">{{ __('Escalate') }}</button>
            </form>
        </div>
    </details>

    @if ($openNotes->isNotEmpty())
        <div class="mt-3">
            <p class="text-[10px] font-semibold uppercase text-slate-500">{{ __('Open follow-ups') }}</p>
            <ul class="mt-2 space-y-2 text-xs">
                @foreach ($openNotes as $cn)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded border border-slate-200 bg-white px-2 py-1.5 dark:border-slate-800 dark:bg-slate-900">
                        <span>
                            <span class="font-semibold">{{ $outcomes[$cn->outcome] ?? ucfirst(str_replace('_', ' ', $cn->outcome ?? $cn->note_type)) }}</span>
                            — {{ Str::limit($cn->displayText(), 60) }}
                            @if ($cn->follow_up_date)
                                <span class="text-slate-500">· {{ __('Follow-up') }} {{ $cn->follow_up_date->format('M j') }}</span>
                            @endif
                            @if ($cn->promise_to_pay_date)
                                <span class="text-emerald-600">· {{ __('Promise') }} {{ $cn->promise_to_pay_date->format('M j') }}</span>
                            @endif
                        </span>
                        <form method="post" action="{{ route('invoices.collection-notes.complete', [$invoice, $cn]) }}">@csrf
                            <button type="submit" class="text-indigo-600 font-semibold">{{ __('Complete follow-up') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
