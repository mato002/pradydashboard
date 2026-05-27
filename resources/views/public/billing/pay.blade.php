<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Pay subscription') }} — {{ $tenant->company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="rounded-2xl border border-indigo-500/30 bg-slate-900/90 p-8 shadow-2xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">{{ __('PradytecAI billing') }}</p>
            <h1 class="mt-2 text-2xl font-semibold">{{ __('Complete your payment') }}</h1>
            <p class="mt-2 text-sm text-slate-400">{{ $tenant->company_name }}</p>

            @if ($billing && ($billing['amount_due'] ?? 0) > 0)
                <div class="mt-6 rounded-xl bg-slate-800/60 p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Amount due') }}</p>
                    <p class="mt-1 text-3xl font-bold tabular-nums text-white">{{ $billing['amount_due_formatted'] }}</p>
                    @if (! empty($billing['invoice_number']))
                        <p class="mt-2 text-sm text-slate-400">{{ __('Invoice') }}: <span class="font-mono font-semibold text-slate-200">{{ $billing['invoice_number'] }}</span></p>
                    @endif
                    @if (! empty($billing['due_date']))
                        <p class="text-sm text-slate-400">{{ __('Due') }}: {{ $billing['due_date'] }}</p>
                    @endif
                </div>
            @endif

            @if ($billing && ! empty($billing['payment_instructions']))
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-slate-200">{{ __('How to pay') }}</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $billing['payment_instructions'] }}</p>
                </div>
            @endif

            @if ($invoices->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-slate-200">{{ __('Open invoices') }}</h2>
                    <ul class="mt-2 divide-y divide-slate-700/80 rounded-xl border border-slate-700/80">
                        @foreach ($invoices as $invoice)
                            <li class="flex items-center justify-between px-4 py-3 text-sm">
                                <span class="font-mono">{{ $invoice->invoice_number }}</span>
                                <span class="font-semibold tabular-nums">{{ $invoice->formattedBalance() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (! $billing)
                <p class="mt-6 text-sm text-slate-400">{{ __('No outstanding balance on file. Contact billing if you believe this is an error.') }}</p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @if ($billing && ! empty($billing['billing_phone']))
                    <a href="tel:{{ preg_replace('/\s+/', '', $billing['billing_phone']) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                        {{ __('Call') }} {{ $billing['billing_phone'] }}
                    </a>
                @endif
                @if ($billing && ! empty($billing['billing_email']))
                    <a href="mailto:{{ $billing['billing_email'] }}" class="inline-flex items-center rounded-xl border border-slate-600 px-5 py-2.5 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                        {{ __('Email billing') }}
                    </a>
                @endif
            </div>

            <p class="mt-8 text-xs text-slate-500">
                {{ __('After payment is recorded in our system, your hosted application access restores automatically within a few minutes.') }}
            </p>
        </div>
    </div>
</body>
</html>
