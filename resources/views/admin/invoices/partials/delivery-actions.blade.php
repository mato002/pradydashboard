@php
    $deliveryVariant = $invoice->deliveryStatusVariant();
    $canMarkSent = $invoice->status === 'draft';
    $wasEmailed = in_array($invoice->delivery_status, ['sent', 'resent'], true);
    $pdfUrl = route('invoices.pdf', $invoice).(request()->filled('template_id') ? '?template_id='.request('template_id') : '');
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Delivery') }}</h3>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-ui.status-badge :variant="$deliveryVariant">{{ $invoice->deliveryStatusLabel() }}</x-ui.status-badge>
                @if ($invoice->finalized_at)
                    <span class="text-[10px] text-slate-500">{{ __('Finalized') }} {{ $invoice->finalized_at->diffForHumans() }}</span>
                @else
                    <span class="text-[10px] text-amber-600 dark:text-amber-400">{{ __('Not finalized') }}</span>
                @endif
            </div>
            @if ($invoice->email_sent_at)
                <p class="mt-1 text-xs text-slate-500">{{ __('Last emailed') }}: {{ $invoice->email_sent_at->format('M j, Y g:i A') }}</p>
            @endif
            @if ($invoice->last_delivery_error)
                <p class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1.5 text-xs text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                    {{ $invoice->last_delivery_error }}
                </p>
            @endif
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @if (! $invoice->finalized_at)
            <form method="post" action="{{ route('invoices.finalize', $invoice) }}">@csrf
                <button type="submit" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950 dark:text-indigo-200">
                    {{ __('Finalize') }}
                </button>
            </form>
        @endif
        @if ($canMarkSent)
            <form method="post" action="{{ route('invoices.mark-sent', $invoice) }}">@csrf
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                    {{ __('Finalize & mark sent') }}
                </button>
            </form>
        @endif
        <a href="{{ $pdfUrl }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
            {{ __('Download PDF') }}
        </a>
    </div>

    <form method="post" action="{{ route('invoices.email', $invoice) }}" class="mt-4 space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
        @csrf
        @if ($wasEmailed)
            <input type="hidden" name="resend" value="1">
        @endif
        <label class="block text-xs font-medium text-slate-500">{{ __('Recipient email') }}</label>
        <div class="flex flex-wrap gap-2">
            <input
                type="email"
                name="recipient_email"
                value="{{ old('recipient_email', $defaultRecipient ?? '') }}"
                placeholder="{{ __('billing@client.com') }}"
                class="min-w-[200px] flex-1 rounded-lg border-slate-300 text-sm dark:bg-slate-950"
            >
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">
                {{ $wasEmailed ? __('Resend email') : __('Send email') }}
            </button>
        </div>
        <p class="text-[10px] text-slate-500">{{ __('PDF is attached. Defaults to tenant billing email or manual client email.') }}</p>
    </form>
</div>
