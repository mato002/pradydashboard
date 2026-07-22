@php
    $isEdit = isset($invoice);
    $typeLabels = [
        'invoice' => __('Invoice'),
        'proforma' => __('Proforma'),
        'quotation' => __('Quotation'),
        'receipt' => __('Receipt'),
    ];
    $typeBadgeVariant = match ($documentType) {
        'quotation' => 'purple',
        'proforma' => 'warning',
        'receipt' => 'success',
        default => 'info',
    };
    if ($isEdit) {
        $documentType = $invoice->document_type;
        $heading = __('Edit :type :number', [
            'type' => $typeLabels[$documentType] ?? __('Document'),
            'number' => $invoice->invoice_number,
        ]);
    } else {
        $heading = __('Create :type', ['type' => $typeLabels[$documentType] ?? __('Document')]);
    }
    $defaultLineItem = [
        'description' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'discount' => 0,
        'tax_rate' => 0,
        'item_type' => 'custom',
    ];
    $initialLineItems = old('line_items');
    if (! is_array($initialLineItems) || $initialLineItems === []) {
        if ($isEdit) {
            $initialLineItems = $invoice->lineItems->map(fn ($line) => [
                'description' => $line->description,
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'discount' => (float) $line->discount,
                'tax_rate' => (float) $line->tax_rate,
                'item_type' => $line->item_type ?? 'custom',
            ])->all();
        }
        if ($initialLineItems === []) {
            $initialLineItems = [$defaultLineItem];
        }
    }
    $formAction = $isEdit ? route('invoices.manual.update', $invoice) : route('invoices.manual.store');
    $cancelUrl = $isEdit ? route('invoices.show', $invoice) : route('invoices.index');
@endphp

<x-dashboard-layout :heading="$heading" :subheading="$isEdit ? __('Update draft document') : __('Manual financial document')">
    <div
        x-data="manualDocumentForm(@js([
            'documentType' => $documentType,
            'currency' => old('currency', $isEdit ? $invoice->currency : $defaultCurrency),
            'lineItems' => $initialLineItems,
            'tenantProfileBase' => url('/invoices/tenants'),
            'oldTenantId' => old('tenant_id', $isEdit ? (string) ($invoice->tenant_id ?? '') : ''),
            'oldSubscriptionId' => old('tenant_project_subscription_id', $isEdit ? (string) ($invoice->tenant_project_subscription_id ?? '') : ''),
            'clientName' => old('manual_client_name', $isEdit ? ($invoice->manual_client_name ?? '') : ''),
            'clientEmail' => old('manual_client_email', $isEdit ? ($invoice->manual_client_email ?? '') : ''),
            'clientPhone' => old('manual_client_phone', $isEdit ? ($invoice->manual_client_phone ?? '') : ''),
            'clientAddress' => old('manual_client_address', $isEdit ? ($invoice->manual_client_address ?? '') : ''),
            'issueDate' => old('issue_date', $isEdit ? $invoice->issue_date?->toDateString() : now()->toDateString()),
            'dueDate' => old('due_date', $isEdit ? $invoice->due_date?->toDateString() : ''),
            'paymentDate' => old('payment_date', now()->toDateString()),
            'notes' => old('notes', $isEdit ? ($invoice->notes ?? '') : ''),
            'linkedInvoiceId' => old('linked_invoice_id', ''),
            'receiptAmount' => old('amount_received', 0),
            'receiptLineDesc' => old('line_description', __('Payment received')),
            'amountPaid' => old('amount_paid', $isEdit ? (float) $invoice->amount_paid : 0),
            'previewCompany' => $previewCompany,
            'paymentOptions' => $paymentOptions,
            'numberPrefix' => \App\Support\Billing\BillingDocumentType::numberPrefix($documentType),
            'previewUrl' => route('invoices.manual.preview'),
            'initialPreviewHtml' => $initialPreviewHtml ?? '',
            'initialPreviewPaperSize' => $initialPreviewPaperSize ?? 'A5',
            'openInvoices' => $openInvoicesPicker ?? [],
            'typeLabels' => $typeLabels,
            'i18n' => [
                'clientName' => __('Client name'),
                'paymentReceived' => __('Payment received'),
            ],
        ]))"
    >
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ $cancelUrl }}" class="text-sm text-indigo-600 hover:underline">← {{ $isEdit ? __('Back to document') : __('Financial operations') }}</a>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="previewOpen = true"
                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 lg:hidden dark:border-indigo-800 dark:bg-slate-900 dark:text-indigo-300"
            >
                {{ __('View Preview') }}
            </button>
            <x-ui.status-badge :variant="$typeBadgeVariant">{{ $typeLabels[$documentType] ?? $documentType }}</x-ui.status-badge>
        </div>
    </div>

    <div
        x-show="previewOpen"
        x-cloak
        @click="previewOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
        aria-hidden="true"
    ></div>

    <div
        id="manual-document-create-layout"
        data-testid="manual-document-create-layout"
        class="manual-document-create-layout"
    >
    <div class="manual-document-create-form">
    <form
        method="post"
        action="{{ $formAction }}"
        class="space-y-6"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="document_type" value="{{ $documentType }}">

        {{-- Client / tenant --}}
        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Client') }}</h3>
            <div class="mt-4">
                <label class="text-xs font-medium text-slate-500">{{ __('Tenant') }}</label>
                <select
                    name="tenant_id"
                    x-model="tenantId"
                    @change="onTenantChange()"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950"
                >
                    <option value="">{{ __('— No tenant (manual client) —') }}</option>
                    @foreach ($tenants as $t)
                        <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div
                x-show="!tenantId"
                x-cloak
                class="mt-4 flex gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100"
                role="alert"
            >
                <x-ui.icon name="triangle-exclamation" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <div>
                    <p class="font-semibold">{{ __('No tenant selected') }}</p>
                    <p class="mt-0.5 text-xs opacity-90">{{ __('This document will not appear under a tenant account. Enter manual client details below. Linked receipts cannot attach to walk-in invoices from this form.') }}</p>
                </div>
            </div>

            <div x-show="tenantId" x-cloak class="mt-4">
                <div x-show="profileLoading" class="text-xs text-slate-500">{{ __('Loading billing profile…') }}</div>
                <div
                    x-show="tenantProfile && !profileLoading"
                    class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/30"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('Billing profile (auto-filled)') }}</p>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Company') }}</dt>
                            <dd class="font-medium text-slate-900 dark:text-white" x-text="tenantProfile?.company_name || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Contact') }}</dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_contact_name || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Email') }}</dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_email || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Phone') }}</dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_phone || '—'"></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-500">{{ __('Address') }}</dt>
                            <dd class="whitespace-pre-line text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_address || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('Currency') }}</dt>
                            <dd class="font-mono font-semibold" x-text="tenantProfile?.currency || currency"></dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-4" x-show="tenantId">
                    <label class="text-xs font-medium text-slate-500">{{ __('Project subscription') }}</label>
                    <select name="tenant_project_subscription_id" x-model="subscriptionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                        <option value="">{{ __('— Optional —') }}</option>
                        <template x-for="sub in subscriptions" :key="sub.id">
                            <option :value="sub.id" x-text="sub.label"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            x-show="!tenantId"
            x-cloak
        >
            <h3 class="text-sm font-semibold">{{ __('Manual client details') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Required when no tenant is selected.') }}</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-500">{{ __('Client name') }} *</label>
                    <input name="manual_client_name" x-model="clientName" value="{{ old('manual_client_name') }}" x-bind:required="!tenantId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Email') }}</label>
                    <input type="email" name="manual_client_email" x-model="clientEmail" value="{{ old('manual_client_email') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Phone') }}</label>
                    <input name="manual_client_phone" x-model="clientPhone" value="{{ old('manual_client_phone') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-slate-500">{{ __('Address') }}</label>
                    <textarea name="manual_client_address" x-model="clientAddress" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">{{ old('manual_client_address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Document details') }}</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="text-xs font-medium text-slate-500">{{ __('Currency') }}</label>
                    <input name="currency" x-model="currency" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500">{{ __('Document date') }}</label>
                    <input type="date" name="issue_date" x-model="issueDate" value="{{ old('issue_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                @if ($documentType !== 'receipt')
                    <div>
                        <label class="text-xs font-medium text-slate-500">{{ __('Due date') }}</label>
                        <input type="date" name="due_date" x-model="dueDate" value="{{ old('due_date') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500">{{ __('Amount paid (optional)') }}</label>
                        <input type="number" step="0.01" min="0" name="amount_paid" x-model.number="amountPaid" value="{{ old('amount_paid', 0) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                @endif
            </div>
        </div>

        @if ($documentType === 'receipt')
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold">{{ __('Receipt payment') }}</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-xs text-slate-500">{{ __('Link to invoice (optional)') }}</label>
                        <select name="linked_invoice_id" x-model="linkedInvoiceId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                            <option value="">{{ __('Standalone receipt') }}</option>
                            <template x-for="inv in filteredOpenInvoices()" :key="inv.id">
                                <option :value="inv.id" x-text="inv.label"></option>
                            </template>
                        </select>
                        <p x-show="tenantId && filteredOpenInvoices().length === 0" x-cloak class="mt-1 text-xs text-slate-500">
                            {{ __('No open invoices for this tenant.') }}
                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('Amount received') }} *</label>
                        <input type="number" step="0.01" min="0.01" name="amount_received" x-model.number="receiptAmount" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('Payment date') }} *</label>
                        <input type="date" name="payment_date" x-model="paymentDate" value="{{ old('payment_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('Payment method') }} *</label>
                        <input name="payment_method" value="{{ old('payment_method', 'bank_transfer') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('Reference') }}</label>
                        <input name="payment_reference" value="{{ old('payment_reference') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div class="sm:col-span-2" x-show="!linkedInvoiceId">
                        <label class="text-xs text-slate-500">{{ __('Line description') }}</label>
                        <input name="line_description" x-model="receiptLineDesc" value="{{ old('line_description', __('Payment received')) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                </div>
                <p class="mt-4 text-right text-sm text-slate-600 dark:text-slate-400">
                    {{ __('Receipt total (preview)') }}: <span class="font-mono text-base font-semibold text-emerald-700 dark:text-emerald-300" x-text="formatMoney(receiptAmount)"></span>
                </p>
            </div>
        @else
            <div class="rounded-2xl border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <h3 class="text-sm font-semibold">{{ __('Line items') }}</h3>
                    <button
                        type="button"
                        @click="addLine()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
                    >
                        <span aria-hidden="true">+</span> {{ __('Add line') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="line-items-table min-w-[720px] w-full text-left text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950/50">
                            <tr>
                                <th class="w-8 px-3 py-2">#</th>
                                <th class="px-3 py-2 min-w-[180px]">{{ __('Description') }}</th>
                                <th class="px-3 py-2 w-28">{{ __('Type') }}</th>
                                <th class="px-3 py-2 w-20 text-right">{{ __('Qty') }}</th>
                                <th class="px-3 py-2 w-24 text-right">{{ __('Unit') }}</th>
                                <th class="px-3 py-2 w-20 text-right">{{ __('Disc.') }}</th>
                                <th class="px-3 py-2 w-16 text-right">{{ __('Tax %') }}</th>
                                <th class="px-3 py-2 w-24 text-right">{{ __('Line') }}</th>
                                <th class="w-24 px-3 py-2 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="(line, index) in lines" :key="line._key">
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30">
                                    <td class="px-3 py-2 text-xs text-slate-400" x-text="index + 1"></td>
                                    <td class="px-3 py-2">
                                        <input :name="'line_items['+index+'][description]'" x-model="line.description" placeholder="{{ __('Description') }}" required class="w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select :name="'line_items['+index+'][item_type]'" x-model="line.item_type" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                                            @foreach ($lineItemTypes as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.0001" min="0" :name="'line_items['+index+'][quantity]'" x-model.number="line.quantity" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][unit_price]'" x-model.number="line.unit_price" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][discount]'" x-model.number="line.discount" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][tax_rate]'" x-model.number="line.tax_rate" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-slate-700 dark:text-slate-300" x-text="formatMoney(lineTotal(line))"></td>
                                    <td class="px-3 py-2 text-right" @click.stop>
                                        <x-ui.row-actions-menu>
                                            <x-ui.row-action @click="duplicateLine(index)">{{ __('Duplicate') }}</x-ui.row-action>
                                            <x-ui.row-action @click="removeLine(index)" x-bind:disabled="lines.length <= 1" danger>{{ __('Remove') }}</x-ui.row-action>
                                        </x-ui.row-actions-menu>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Live total preview') }} <span class="font-normal normal-case">({{ __('server calculates on save') }})</span></p>
                    <dl class="ml-auto max-w-xs space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().subtotal)"></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Discount') }}</dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().discount)"></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ __('Tax') }}</dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().tax)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-2 dark:border-slate-700">
                            <dt class="font-semibold text-slate-900 dark:text-white">{{ __('Total') }}</dt>
                            <dd class="font-mono text-base font-semibold tabular-nums text-indigo-700 dark:text-indigo-300" x-text="formatMoney(totals().total)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 text-xs" x-show="amountPaid > 0">
                            <dt class="text-slate-500">{{ __('Amount paid') }}</dt>
                            <dd class="font-mono tabular-nums text-emerald-700 dark:text-emerald-300" x-text="formatMoney(amountPaid)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 text-xs" x-show="amountPaid > 0">
                            <dt class="text-slate-500">{{ __('Balance (preview)') }}</dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(Math.max(0, totals().total - amountPaid))"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        <div>
            <label class="text-xs text-slate-500">{{ __('Notes') }}</label>
            <textarea name="notes" x-model="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">{{ old('notes') }}</textarea>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                {{ $isEdit ? __('Save changes') : __('Save as draft') }}
            </button>
            <a href="{{ $cancelUrl }}" class="rounded-xl border px-5 py-2.5 text-sm font-semibold">{{ __('Cancel') }}</a>
        </div>
    </form>
    </div>

    <div class="manual-document-create-preview">
        @include('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'defaultTemplate' => $defaultTemplate,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'desktop',
        ])
    </div>
    </div>

    <div class="manual-document-create-preview-mobile lg:hidden">
        @include('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'defaultTemplate' => $defaultTemplate,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'mobile',
        ])
    </div>
    </div>

    @once
        <style>
            #manual-document-create-layout .line-items-table input[type=number]::-webkit-outer-spin-button,
            #manual-document-create-layout .line-items-table input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            #manual-document-create-layout .line-items-table input[type=number] {
                -moz-appearance: textfield;
                appearance: textfield;
            }
        </style>
    @endonce
</x-dashboard-layout>
