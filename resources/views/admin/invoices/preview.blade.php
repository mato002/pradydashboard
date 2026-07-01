<x-dashboard-layout :heading="$invoice->invoice_number" :subheading="__('Document preview')">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Back to invoice') }}</a>
        <form method="post" action="{{ route('invoices.regenerate', $invoice) }}" class="inline flex items-center gap-2">@csrf
            @if ($invoice->canRegenerate())
                <button
                    type="submit"
                    class="rounded-lg border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:text-amber-200"
                    @if ($invoice->wasEmailed()) onclick="return confirm(@js(__('This document was already emailed. Regenerating creates a new revision. Continue?')))" @endif
                >
                    {{ __('Save PDF snapshot') }}
                </button>
            @else
                <span class="text-xs text-slate-500">{{ $invoice->regenerateBlockedReason() }}</span>
            @endif
        </form>
    </div>

    <div
        id="invoice-preview-layout"
        data-testid="invoice-preview-layout"
        class="invoice-document-split-layout"
    >
        <div class="invoice-document-split-main space-y-4">
            @include('admin.invoices.partials.delivery-actions', [
                'invoice' => $invoice,
                'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
            ])

            @if ($invoice->balanceDue() > 0.009 && ! in_array($invoice->status, ['paid', 'cancelled', 'void', 'draft']))
                @include('admin.invoices.partials.collection-actions', [
                    'invoice' => $invoice->loadMissing('collectionNotes'),
                    'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
                ])
            @endif

            <div class="invoice-document-split-preview-mobile lg:hidden">
                <p class="mb-2 text-xs font-medium text-slate-500">{{ __('Document preview') }}</p>
                <x-billing.document-preview-frame
                    :html="$previewHtml"
                    :paper-size="$documentTemplate->paper_size"
                    :title="__('Preview')"
                />
            </div>
        </div>

        <div class="invoice-document-split-preview">
            <div class="invoice-document-split-preview-inner">
                <p class="mb-2 hidden text-xs font-medium text-slate-500 lg:block">{{ __('Document preview') }}</p>
                <x-billing.document-preview-frame
                    :html="$previewHtml"
                    :paper-size="$documentTemplate->paper_size"
                    :title="__('Preview')"
                />
            </div>
        </div>
    </div>
</x-dashboard-layout>
