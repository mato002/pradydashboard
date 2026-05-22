@php
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
    $heading = __('Create :type', ['type' => $typeLabels[$documentType] ?? __('Document')]);
@endphp

<x-dashboard-layout :heading="$heading" :subheading="__('Manual financial document')">
    @php
        $defaultTemplateId = old('document_template_id', $templates->firstWhere('is_default')?->id ?? $templates->first()?->id);
    @endphp

    <div
        x-data="manualDocumentForm(@js([
            'documentType' => $documentType,
            'currency' => old('currency', $defaultCurrency),
            'lineItems' => old('line_items', [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0, 'tax_rate' => 0, 'item_type' => 'custom']]),
            'tenantProfileBase' => url('/invoices/tenants'),
            'oldTenantId' => old('tenant_id', ''),
            'oldSubscriptionId' => old('tenant_project_subscription_id', ''),
            'oldTemplateId' => $defaultTemplateId,
            'templates' => $templatesMeta,
            'previewCompany' => $previewCompany,
            'paymentOptions' => $paymentOptions,
            'numberPrefix' => \App\Support\Billing\BillingDocumentType::numberPrefix($documentType),
        ]))"
    >
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:underline">← {{ __('Financial operations') }}</a>
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
        action="{{ route('invoices.manual.store') }}"
        class="space-y-6"
    >
        @csrf
        <input type="hidden" name="document_type" value="{{ $documentType }}">

        {{-- Document template --}}
        <div class="rounded-2xl border-2 border-indigo-200 bg-indigo-50/40 p-5 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/30">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Document template') }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Layout used for preview and PDF. You can change it after saving.') }}</p>
                </div>
                @if ($templates->isNotEmpty())
                    <span class="rounded-full bg-white px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-700 ring-1 ring-indigo-200 dark:bg-slate-900 dark:text-indigo-300 dark:ring-indigo-800">
                        {{ trans_choice(':count template|:count templates', $templates->count(), ['count' => $templates->count()]) }}
                    </span>
                @endif
            </div>
            @if ($templates->isEmpty())
                <p class="mt-3 text-sm text-amber-800 dark:text-amber-200">{{ __('No active templates for this document type. Add one under Templates tab.') }}</p>
            @else
                <select
                    name="document_template_id"
                    x-model="templateId"
                    class="mt-4 w-full rounded-lg border-indigo-200 bg-white text-sm font-medium dark:border-indigo-800 dark:bg-slate-950"
                >
                    @foreach ($templates as $tpl)
                        <option value="{{ $tpl->id }}" @selected(old('document_template_id', $templates->firstWhere('is_default')?->id) == $tpl->id)>
                            {{ $tpl->name }}
                            @if ($tpl->is_default) — {{ __('Default') }} @endif
                            ({{ strtoupper($tpl->paper_size) }})
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Client / tenant --}}
        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Client') }}</h3>
            <div class="mt-4">
                <label class="text-xs font-medium text-slate-500">{{ __('Tenant') }}</label>
                <select
                    name="tenant_id"
                    x-model="tenantId"
                    @change="loadTenant()"
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
                <span class="text-lg leading-none" aria-hidden="true">⚠</span>
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
                            @foreach ($openInvoices as $inv)
                                <option value="{{ $inv->id }}" @selected(old('linked_invoice_id') == $inv->id)>
                                    {{ $inv->invoice_number }} — {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                                </option>
                            @endforeach
                        </select>
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
                    <table class="min-w-[720px] w-full text-left text-sm">
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
                                    <td class="px-3 py-2 text-right">
                                        <div class="inline-flex gap-1">
                                            <button type="button" @click="duplicateLine(index)" title="{{ __('Duplicate') }}" class="rounded border px-1.5 py-0.5 text-[10px] font-medium text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">⧉</button>
                                            <button
                                                type="button"
                                                @click="removeLine(index)"
                                                :disabled="lines.length <= 1"
                                                title="{{ __('Remove') }}"
                                                class="rounded border border-rose-200 px-1.5 py-0.5 text-[10px] font-medium text-rose-700 hover:bg-rose-50 disabled:opacity-30 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950"
                                            >{{ __('Remove') }}</button>
                                        </div>
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
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">{{ __('Save as draft') }}</button>
            <a href="{{ route('invoices.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-semibold">{{ __('Cancel') }}</a>
        </div>
    </form>
    </div>

    <div class="manual-document-create-preview">
        @include('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'templates' => $templates,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'desktop',
        ])
    </div>
    </div>

    <div class="manual-document-create-preview-mobile lg:hidden">
        @include('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'templates' => $templates,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'mobile',
        ])
    </div>
    </div>

    <script>
        function manualDocumentForm(config) {
            const profileUrlBase = (config.tenantProfileBase || '').replace(/\/$/, '');
            const templates = config.templates || [];
            const defaultTemplateId = templates.length
                ? String(config.oldTemplateId || templates.find(t => t.is_default)?.id || templates[0].id)
                : '';
            return {
                documentType: config.documentType,
                currency: config.currency,
                templates,
                previewCompany: config.previewCompany || {},
                paymentOptions: config.paymentOptions || {},
                numberPrefix: config.numberPrefix || 'INV',
                templateId: defaultTemplateId,
                tenantId: String(config.oldTenantId || ''),
                subscriptionId: String(config.oldSubscriptionId || ''),
                clientName: @json(old('manual_client_name', '')),
                clientEmail: @json(old('manual_client_email', '')),
                clientPhone: @json(old('manual_client_phone', '')),
                clientAddress: @json(old('manual_client_address', '')),
                issueDate: @json(old('issue_date', now()->toDateString())),
                dueDate: @json(old('due_date', '')),
                paymentDate: @json(old('payment_date', now()->toDateString())),
                notes: @json(old('notes', '')),
                linkedInvoiceId: @json(old('linked_invoice_id', '')),
                receiptAmount: parseFloat(@json(old('amount_received', 0))) || 0,
                receiptLineDesc: @json(old('line_description', __('Payment received'))),
                amountPaid: parseFloat(@json(old('amount_paid', 0))) || 0,
                previewOpen: false,
                previewZoom: 1,
                tenantProfile: null,
                profileLoading: false,
                subscriptions: [],
                lines: config.lineItems.map((l, i) => ({
                    _key: 'line-' + i + '-' + Date.now(),
                    description: l.description || '',
                    quantity: parseFloat(l.quantity) || 1,
                    unit_price: parseFloat(l.unit_price) || 0,
                    discount: parseFloat(l.discount) || 0,
                    tax_rate: parseFloat(l.tax_rate) || 0,
                    item_type: l.item_type || 'custom',
                })),
                selectedTemplate() {
                    if (!this.templates.length) return null;
                    return this.templates.find(t => String(t.id) === String(this.templateId)) || this.templates[0];
                },
                previewLayout() {
                    const style = this.selectedTemplate()?.style || '';
                    if (style === 'prady_classic_a5') return 'prady_classic_a5';
                    if (style === 'thermal_receipt') return 'thermal_receipt';
                    return 'generic';
                },
                previewBranding() {
                    return this.selectedTemplate()?.branding || {};
                },
                documentTypeBadge() {
                    const labels = { invoice: @json(__('Invoice')), proforma: @json(__('Proforma')), quotation: @json(__('Quotation')), receipt: @json(__('Receipt')) };
                    return labels[this.documentType] || this.documentType;
                },
                documentPreviewTitle() {
                    const titles = {
                        invoice: 'INVOICE',
                        proforma: 'PROFORMA INVOICE',
                        quotation: 'QUOTATION',
                        receipt: 'RECEIPT',
                    };
                    return titles[this.documentType] || 'DOCUMENT';
                },
                previewDocNumber() {
                    return this.numberPrefix + '-DRAFT-0000';
                },
                previewIssueDate() {
                    return this.issueDate || '—';
                },
                previewDueDate() {
                    return this.dueDate || '';
                },
                previewPaymentDate() {
                    return this.paymentDate || this.issueDate || '';
                },
                previewClientName() {
                    if (this.tenantId && this.tenantProfile?.company_name) {
                        return this.tenantProfile.company_name;
                    }
                    const name = (this.clientName || '').trim();
                    return name || @json(__('Client name'));
                },
                previewClientAttention() {
                    if (this.tenantId && this.tenantProfile?.billing_contact_name) {
                        const c = this.tenantProfile.billing_contact_name;
                        if (c && c !== this.previewClientName()) return c;
                    }
                    return '';
                },
                previewClientEmail() {
                    if (this.tenantId) return this.tenantProfile?.billing_email || '';
                    return (this.clientEmail || '').trim();
                },
                previewClientPhone() {
                    if (this.tenantId) return this.tenantProfile?.billing_phone || '';
                    return (this.clientPhone || '').trim();
                },
                previewClientAddress() {
                    if (this.tenantId) return this.tenantProfile?.billing_address || '';
                    return (this.clientAddress || '').trim();
                },
                receiptLineDescription() {
                    return (this.receiptLineDesc || '').trim() || @json(__('Payment received'));
                },
                formatQty(qty) {
                    const n = parseFloat(qty) || 0;
                    return n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 4 });
                },
                formatAmount(amount) {
                    const n = parseFloat(amount) || 0;
                    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                lineSubtotal(line) {
                    return Math.max(0, (line.quantity * line.unit_price) - line.discount);
                },
                lineTax(line) {
                    return Math.round(this.lineSubtotal(line) * (line.tax_rate / 100) * 100) / 100;
                },
                lineTotal(line) {
                    return Math.round((this.lineSubtotal(line) + this.lineTax(line)) * 100) / 100;
                },
                totals() {
                    let subtotal = 0, discount = 0, tax = 0;
                    this.lines.forEach(line => {
                        const sub = this.lineSubtotal(line);
                        subtotal += sub;
                        discount += Math.max(0, parseFloat(line.discount) || 0);
                        tax += this.lineTax(line);
                    });
                    subtotal = Math.round(subtotal * 100) / 100;
                    tax = Math.round(tax * 100) / 100;
                    return {
                        subtotal,
                        discount: Math.round(discount * 100) / 100,
                        tax,
                        total: Math.round((subtotal + tax) * 100) / 100,
                    };
                },
                formatMoney(amount) {
                    const n = parseFloat(amount) || 0;
                    return (this.currency || 'KES') + ' ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                addLine() {
                    this.lines.push({
                        _key: 'line-' + Date.now() + '-' + Math.random().toString(36).slice(2),
                        description: '',
                        quantity: 1,
                        unit_price: 0,
                        discount: 0,
                        tax_rate: 0,
                        item_type: 'custom',
                    });
                },
                duplicateLine(index) {
                    const src = this.lines[index];
                    this.lines.splice(index + 1, 0, {
                        _key: 'line-' + Date.now() + '-' + Math.random().toString(36).slice(2),
                        description: src.description,
                        quantity: src.quantity,
                        unit_price: src.unit_price,
                        discount: src.discount,
                        tax_rate: src.tax_rate,
                        item_type: src.item_type,
                    });
                },
                removeLine(i) {
                    if (this.lines.length > 1) this.lines.splice(i, 1);
                },
                async loadTenant() {
                    if (!this.tenantId) {
                        this.tenantProfile = null;
                        this.subscriptions = [];
                        this.subscriptionId = '';
                        return;
                    }
                    this.profileLoading = true;
                    try {
                        const res = await fetch(profileUrlBase + this.tenantId + '/billing-profile');
                        if (!res.ok) throw new Error('profile');
                        const data = await res.json();
                        this.tenantProfile = data;
                        this.clientEmail = data.billing_email || '';
                        this.clientPhone = data.billing_phone || '';
                        this.clientAddress = data.billing_address || '';
                        this.clientName = data.company_name || '';
                        this.subscriptions = data.subscriptions || [];
                        if (data.currency) this.currency = data.currency;
                        if (this.subscriptionId && !this.subscriptions.some(s => String(s.id) === String(this.subscriptionId))) {
                            this.subscriptionId = '';
                        }
                    } catch {
                        this.tenantProfile = null;
                    } finally {
                        this.profileLoading = false;
                    }
                },
                init() {
                    if (this.tenantId) this.loadTenant();
                    if (!this.templateId && this.templates.length) {
                        this.templateId = String(this.templates[0].id);
                    }
                },
            };
        }
    </script>
</x-dashboard-layout>
