@php
    $initialTemplate = $templates->firstWhere('is_default') ?? $templates->first();
    $initialDocTitle = match ($documentType) {
        'proforma' => 'PROFORMA INVOICE',
        'quotation' => 'QUOTATION',
        'receipt' => 'RECEIPT',
        default => 'INVOICE',
    };
@endphp

@php
    $isMobilePreview = ($previewMode ?? 'desktop') === 'mobile';
@endphp

@if (! $isMobilePreview)
    @once
        @include('admin.invoices.partials.manual-document-preview-styles')
    @endonce
@endif

<aside
    @if (! $isMobilePreview) id="manual-document-preview" @endif
    data-testid="manual-document-preview"
    data-preview-mode="{{ $previewMode ?? 'desktop' }}"
    data-initial-doc-type="{{ $documentType }}"
    @class([
        'manual-doc-preview-panel flex w-full max-w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-900/60',
        'sticky top-24 max-h-[calc(100vh-7rem)]' => ! $isMobilePreview,
        'fixed inset-y-0 right-0 z-50 hidden max-w-md shadow-2xl' => $isMobilePreview,
    ])
    @if ($isMobilePreview)
        x-show="previewOpen"
        x-cloak
        :class="{ '!flex': previewOpen }"
    @endif
>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200" x-text="documentTypeBadge()">{{ $typeLabels[$documentType] ?? $documentType }}</span>
            <span class="text-xs font-medium text-slate-600 dark:text-slate-300" x-text="selectedTemplate()?.name || @js($initialTemplate?->name ?? __('No template'))">{{ $initialTemplate?->name ?? __('No template') }}</span>
            <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-700" x-text="(selectedTemplate()?.paper_size || 'A5').toUpperCase()">{{ strtoupper($initialTemplate?->paper_size ?? 'A5') }}</span>
        </div>
        <div class="flex items-center gap-1">
            <button type="button" @click="previewZoom = Math.max(0.6, previewZoom - 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="{{ __('Zoom out') }}">−</button>
            <span class="min-w-[3rem] text-center text-[10px] text-slate-500" x-text="Math.round(previewZoom * 100) + '%'">100%</span>
            <button type="button" @click="previewZoom = Math.min(1.2, previewZoom + 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="{{ __('Zoom in') }}">+</button>
            <button type="button" @click="previewOpen = false" class="lg:hidden rounded border px-2 py-0.5 text-xs text-slate-600">{{ __('Close') }}</button>
        </div>
    </div>

    <p class="px-4 pt-2 text-[10px] text-slate-500 dark:text-slate-400">
        {{ __('Preview only — final totals calculated on save') }}
    </p>

    <div class="manual-doc-preview-viewport flex-1 overflow-x-hidden overflow-y-auto p-3">
        <div
            class="manual-doc-preview-scaler mx-auto w-full max-w-full"
            :style="'transform: scale(' + previewZoom + '); transform-origin: top center;'"
        >
            {{-- Prady Classic A5 --}}
            <div
                x-show="previewLayout() === 'prady_classic_a5'"
                x-cloak
                class="manual-doc-preview-frame is-a5 preview-draft-watermark"
                data-preview-layout="prady_classic_a5"
            >
                <div class="preview-a5">
                    <div class="phdr">
                        <div class="phdr-mark" aria-hidden="true" x-show="!previewBranding().logo_url">P</div>
                        <img x-show="previewBranding().logo_url" :src="previewBranding().logo_url" alt="" class="max-h-9 max-w-[120px] object-contain" style="display:none">
                        <div class="phdr-text">
                            <div class="phdr-name" x-text="previewBranding().display_name || previewCompany.display_name"></div>
                            <div class="phdr-tag" x-show="previewBranding().tagline" x-text="previewBranding().tagline"></div>
                            <div class="phdr-meta">
                                <span x-show="previewBranding().tax_pin || previewCompany.tax_pin">
                                    {{ __('PIN') }}: <span x-text="previewBranding().tax_pin || previewCompany.tax_pin"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pdoc-type" data-testid="preview-doc-title" x-text="documentPreviewTitle()">{{ $initialDocTitle }}</div>

                    <div class="pgrid">
                        <div class="pcol">
                            <div class="plab">{{ __('Bill to') }}</div>
                            <div class="pval pstrong" x-text="previewClientName()">{{ __('Sample client') }}</div>
                            <div class="psub" x-show="previewClientAttention()" x-text="'{{ __('Attn.') }} ' + previewClientAttention()"></div>
                            <div class="psub" x-show="previewClientAddress()" x-text="previewClientAddress()"></div>
                            <div class="psub" x-show="previewClientPhone()" x-text="previewClientPhone()"></div>
                            <div class="psub" x-show="previewClientEmail()" x-text="previewClientEmail()"></div>
                        </div>
                        <div class="pcol pcol-num">
                            <div class="pri-row"><span class="plab">{{ __('No.') }}</span><span class="pval mono" x-text="previewDocNumber()">DRAFT-0000</span></div>
                            <div class="pri-row"><span class="plab">{{ __('Date') }}</span><span class="pval" x-text="previewIssueDate()">—</span></div>
                            <template x-if="documentType !== 'receipt'">
                                <div class="pri-row"><span class="plab">{{ __('Due') }}</span><span class="pval" x-text="previewDueDate() || '—'"></span></div>
                            </template>
                            <template x-if="documentType === 'receipt'">
                                <div class="pri-row"><span class="plab">{{ __('Paid') }}</span><span class="pval" x-text="previewPaymentDate() || '—'"></span></div>
                            </template>
                        </div>
                    </div>

                    <template x-if="documentType === 'receipt'">
                        <div>
                            <table class="ptable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Description') }}</th>
                                        <th class="r">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td x-text="receiptLineDescription()"></td>
                                        <td class="r mono" x-text="formatMoney(receiptAmount)"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="ptotals">
                                <div class="ptot-row pgrand"><span>{{ __('Amount received') }}</span><span class="mono" x-text="formatMoney(receiptAmount)"></span></div>
                            </div>
                        </div>
                    </template>

                    <template x-if="documentType !== 'receipt'">
                        <div>
                            <table class="ptable">
                                <thead>
                                    <tr>
                                        <th class="c">#</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="r">{{ __('Qty') }}</th>
                                        <th class="r">{{ __('Unit') }}</th>
                                        <th class="r">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="lines.length === 0 || lines.every(l => !l.description)">
                                        <tr class="empty-row">
                                            <td colspan="5" class="c">{{ __('Line items will appear here') }}</td>
                                        </tr>
                                    </template>
                                    <template x-for="(line, index) in lines" :key="line._key">
                                        <tr x-show="line.description || line.quantity || line.unit_price">
                                            <td class="c muted" x-text="index + 1"></td>
                                            <td x-text="line.description || '—'"></td>
                                            <td class="r mono" x-text="formatQty(line.quantity)"></td>
                                            <td class="r mono" x-text="formatAmount(line.unit_price)"></td>
                                            <td class="r mono" x-text="formatAmount(lineTotal(line))"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="ptotals">
                                <div class="ptot-row"><span>{{ __('Subtotal') }}</span><span class="mono" x-text="formatMoney(totals().subtotal)"></span></div>
                                <div class="ptot-row" x-show="totals().discount > 0"><span>{{ __('Discount') }}</span><span class="mono">− <span x-text="formatAmount(totals().discount)"></span></span></div>
                                <div class="ptot-row"><span>{{ __('Tax') }}</span><span class="mono" x-text="formatMoney(totals().tax)"></span></div>
                                <div class="ptot-row pgrand"><span>{{ __('Total due') }}</span><span class="mono" x-text="formatMoney(totals().total)"></span></div>
                                <div class="ptot-row" x-show="amountPaid > 0"><span>{{ __('Paid') }}</span><span class="mono" x-text="formatMoney(amountPaid)"></span></div>
                                <div class="ptot-row" x-show="amountPaid > 0"><span>{{ __('Balance') }}</span><span class="mono" x-text="formatMoney(Math.max(0, totals().total - amountPaid))"></span></div>
                            </div>
                        </div>
                    </template>

                    <div class="ppay" x-show="documentType !== 'receipt'">
                        <div class="plab">{{ __('Payment options') }}</div>
                        <div class="ppay-grid">
                            <div><span class="pk">{{ __('Bank') }}</span><span class="pv" x-text="paymentOptions.bank_name || '—'"></span></div>
                            <div><span class="pk">{{ __('Account no.') }}</span><span class="pv mono" x-text="paymentOptions.bank_account_number || '—'"></span></div>
                            <div><span class="pk">{{ __('M-Pesa Paybill') }}</span><span class="pv mono" x-text="paymentOptions.mpesa_paybill || '—'"></span></div>
                            <div><span class="pk">{{ __('Paybill account') }}</span><span class="pv mono" x-text="paymentOptions.paybill_account_number || '—'"></span></div>
                        </div>
                    </div>

                    <div class="pnotes" x-show="notes" x-text="notes"></div>
                    <div class="pfoot">
                        <span x-text="previewBranding().footer_text || previewCompany.footer_text || ''"></span>
                        <div class="pinst" x-show="previewCompany.payment_instructions" x-text="previewCompany.payment_instructions"></div>
                    </div>
                </div>
            </div>

            {{-- Thermal receipt layout --}}
            <div
                x-show="previewLayout() === 'thermal_receipt'"
                x-cloak
                class="manual-doc-preview-frame is-thermal preview-draft-watermark"
                data-preview-layout="thermal_receipt"
            >
                <div class="preview-thermal">
                    <p style="font-weight:bold;" x-text="previewBranding().display_name || previewCompany.display_name"></p>
                    <p class="pdoc-type" style="text-align:center;font-size:10px;letter-spacing:.1em;" x-text="documentPreviewTitle()">{{ $initialDocTitle }}</p>
                    <p class="mono" x-text="previewDocNumber()">DRAFT-0000</p>
                    <hr style="border:none;border-top:1px dashed #94a3b8;margin:6px 0;">
                    <p x-text="previewClientName()"></p>
                    <div class="t-lines">
                        <template x-if="documentType === 'receipt'">
                            <div class="t-line"><span x-text="receiptLineDescription()"></span><span x-text="formatMoney(receiptAmount)"></span></div>
                        </template>
                        <template x-if="documentType !== 'receipt'">
                            <template x-for="(line, index) in lines" :key="'t-'+line._key">
                                <div class="t-line" x-show="line.description || line.unit_price">
                                    <span x-text="line.description || ('{{ __('Line') }} ' + (index+1))"></span>
                                    <span x-text="formatMoney(lineTotal(line))"></span>
                                </div>
                            </template>
                        </template>
                    </div>
                    <hr style="border:none;border-top:1px dashed #94a3b8;margin:6px 0;">
                    <p class="t-total">{{ __('TOTAL') }} <span x-text="formatMoney(documentType === 'receipt' ? receiptAmount : totals().total)"></span></p>
                    <p class="pnotes text-left" style="font-size:9px;margin-top:8px;" x-show="notes" x-text="notes"></p>
                </div>
            </div>

            {{-- Generic A5 fallback --}}
            <div
                x-show="previewLayout() === 'generic'"
                x-cloak
                class="manual-doc-preview-frame is-generic preview-draft-watermark"
                data-preview-layout="generic"
            >
                <div class="preview-generic preview-a5">
                    <div class="g-hdr">
                        <strong x-text="previewBranding().display_name || previewCompany.display_name"></strong>
                        <div class="g-title" x-text="documentPreviewTitle()">{{ $initialDocTitle }}</div>
                    </div>
                    <p><strong>{{ __('Bill to') }}:</strong> <span x-text="previewClientName()"></span></p>
                    <p class="text-xs"><span x-text="previewDocNumber()"></span> · <span x-text="previewIssueDate()"></span></p>
                    <table class="g-table">
                        <thead><tr><th>#</th><th>{{ __('Description') }}</th><th class="r">{{ __('Amount') }}</th></tr></thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="'g-'+line._key">
                                <tr x-show="documentType !== 'receipt' && (line.description || line.unit_price)">
                                    <td x-text="index + 1"></td>
                                    <td x-text="line.description || '—'"></td>
                                    <td class="r" x-text="formatMoney(lineTotal(line))"></td>
                                </tr>
                            </template>
                            <tr x-show="documentType === 'receipt'">
                                <td>1</td>
                                <td x-text="receiptLineDescription()"></td>
                                <td class="r" x-text="formatMoney(receiptAmount)"></td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-right font-bold">{{ __('Total') }}: <span x-text="formatMoney(documentType === 'receipt' ? receiptAmount : totals().total)"></span></p>
                    <p x-show="notes" x-text="notes" style="margin-top:8px;font-size:9px;"></p>
                </div>
            </div>
        </div>
    </div>
</aside>
