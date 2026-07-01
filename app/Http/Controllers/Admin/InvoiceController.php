<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Billing\BillingSettings;
use App\Domain\Billing\CollectionReminderService;
use App\Domain\Billing\CollectionWorkflowService;
use App\Domain\Billing\DocumentDeliveryService;
use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\DocumentSnapshotBuilder;
use App\Domain\Billing\DocumentTemplateResolver;
use App\Domain\Billing\DraftInvoiceGenerator;
use App\Domain\Billing\FinancialOperationsQuery;
use App\Domain\Billing\ManualDocumentCreator;
use App\Domain\Billing\ManualDocumentPreviewBuilder;
use App\Domain\Billing\OverdueBillingProcessor;
use App\Domain\Billing\PaymentInboxPresenter;
use App\Domain\Billing\PaymentReconciliationQuery;
use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Billing\ProformaConverter;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Billing\RecurringBillingProcessor;
use App\Domain\Billing\SampleFinancialDocumentSnapshot;
use App\Domain\Rbac\RbacScopeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManualFinancialDocumentRequest;
use App\Http\Requests\ManualDocumentPreviewRequest;
use App\Http\Requests\PromiseToPayRequest;
use App\Http\Requests\SendFinancialDocumentRequest;
use App\Http\Requests\StoreCollectionNoteRequest;
use App\Models\CollectionNote;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\InvoiceRecurringSchedule;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\PaymentSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    private const TABS = [
        'overview',
        'invoices',
        'quotations',
        'proforma',
        'receipts',
        'recurring',
        'collections',
        'templates',
        'statements',
        'automation',
        'activity',
        'payments',
    ];

    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly ActivityLogQuery $activityQuery,
        private readonly FinancialOperationsQuery $operations,
    ) {}

    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true)
            ? $request->query('tab')
            : 'overview';

        $scopeFilter = app(RbacScopeFilter::class);
        $kpis = $this->operations->overviewKpis($scopeFilter);

        $data = [
            'tab' => $tab,
            'kpis' => $kpis,
            'filterTenants' => $this->operations->filterTenants($scopeFilter),
            'invoiceTrend' => $this->operations->invoiceTrendSeries($scopeFilter),
            'revenueSeries' => $this->operations->revenueSeries($scopeFilter),
            'agingBuckets' => $this->operations->agingBuckets($scopeFilter),
            'automation' => $this->operations->automationStats($scopeFilter),
            'alerts' => $this->operations->alerts($scopeFilter),
            'topDebtors' => $this->operations->topDebtors($scopeFilter),
            'upcomingRenewals' => $this->operations->upcomingRenewals($scopeFilter),
            'failedDeliveries' => $this->operations->failedDeliveries($scopeFilter),
            'expiringSubscriptions' => $this->operations->expiringSubscriptions($scopeFilter),
            'schedules' => $this->operations->schedules($scopeFilter),
            'templates' => $this->operations->templates(),
            'automationRules' => $this->operations->automationRules(),
            'collectionNotes' => $this->operations->recentCollectionNotes($scopeFilter),
            'activityLogs' => $this->operations->activityLogs($scopeFilter),
            'documentTypes' => BillingDocumentType::all(),
        ];

        $data['invoices'] = match ($tab) {
            'quotations' => $this->operations->invoiceRegister($request, $scopeFilter, BillingDocumentType::QUOTATION),
            'proforma' => $this->operations->invoiceRegister($request, $scopeFilter, BillingDocumentType::PROFORMA),
            'receipts' => $this->operations->invoiceRegister($request, $scopeFilter, BillingDocumentType::RECEIPT),
            'statements' => $this->operations->invoiceRegister($request, $scopeFilter, BillingDocumentType::STATEMENT),
            'invoices', 'collections' => $this->operations->invoiceRegister(
                $request,
                $scopeFilter,
                $tab === 'collections' ? BillingDocumentType::INVOICE : null,
            ),
            default => $this->operations->invoiceRegister($request, $scopeFilter, BillingDocumentType::INVOICE),
        };

        if ($tab === 'collections') {
            $data['collections'] = $this->operations->collectionsOverview($scopeFilter);
            $data['overdueInvoices'] = $data['collections']['overdue'];
        }

        if ($tab === 'payments') {
            $paymentQuery = app(PaymentReconciliationQuery::class);
            $data['paymentInbox'] = $paymentQuery->inbox($request, $scopeFilter);
            $data['paymentInboxMeta'] = app(PaymentInboxPresenter::class)
                ->metaForPayments($data['paymentInbox']->getCollection());
            $data['paymentKpis'] = $paymentQuery->inboxKpis($scopeFilter);
            $data['paymentSources'] = PaymentSource::all();
        }

        return view('admin.invoices.index', $data);
    }

    public function create(Request $request): View
    {
        $documentType = $request->query('type', BillingDocumentType::INVOICE);
        if (! in_array($documentType, [
            BillingDocumentType::INVOICE,
            BillingDocumentType::PROFORMA,
            BillingDocumentType::QUOTATION,
            BillingDocumentType::RECEIPT,
        ], true)) {
            $documentType = BillingDocumentType::INVOICE;
        }

        $scopeFilter = app(RbacScopeFilter::class);
        $tenants = $this->operations->filterTenants($scopeFilter);
        $templateResolver = app(DocumentTemplateResolver::class);
        $defaultTemplate = null;
        $defaultTemplateId = $templateResolver->defaultTemplateId($documentType);
        if ($defaultTemplateId) {
            $defaultTemplate = DocumentTemplate::query()->find($defaultTemplateId);
        }

        $openInvoices = TenantInvoice::query()
            ->whereIn('document_type', [BillingDocumentType::INVOICE, BillingDocumentType::PROFORMA])
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->when(! $scopeFilter->isGlobalScope(), function ($q) use ($scopeFilter) {
                $q->whereIn('tenant_id', $scopeFilter->applyTenantScope(Tenant::query())->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'invoice_number', 'tenant_id', 'total', 'currency']);

        $billing = app(BillingSettings::class);
        $issuer = app(\App\Domain\Billing\DocumentIssuerResolver::class)->resolve();
        $initialPreviewHtml = '';
        $initialPreviewPaperSize = 'A5';

        if ($defaultTemplate) {
            $previewBuilder = app(ManualDocumentPreviewBuilder::class);
            $renderer = app(DocumentRenderer::class);
            $initialPreviewHtml = $renderer->render(
                $defaultTemplate,
                $previewBuilder->build([
                    'document_type' => $documentType,
                    'issue_date' => now()->toDateString(),
                    'currency' => $billing->defaultCurrency(),
                    'manual_client_name' => '',
                    'line_items' => [[
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 0,
                        'discount' => 0,
                        'tax_rate' => 0,
                        'item_type' => 'custom',
                    ]],
                ]),
            );
            $initialPreviewPaperSize = strtoupper((string) $defaultTemplate->paper_size);
        }

        return view('admin.invoices.create', [
            'documentType' => $documentType,
            'tenants' => $tenants,
            'defaultTemplate' => $defaultTemplate,
            'openInvoicesPicker' => $openInvoices->map(fn (TenantInvoice $inv) => [
                'id' => $inv->id,
                'tenant_id' => $inv->tenant_id,
                'label' => sprintf(
                    '%s — %s %s',
                    $inv->invoice_number,
                    $inv->currency,
                    number_format((float) $inv->total, 2),
                ),
            ])->values()->all(),
            'lineItemTypes' => ManualDocumentCreator::LINE_ITEM_TYPES,
            'defaultCurrency' => $billing->defaultCurrency(),
            'previewCompany' => [
                'display_name' => $issuer['display_name'] ?? $billing->tradingName(),
                'legal_name' => $issuer['legal_name'] ?? $billing->companyLegalName(),
                'tax_pin' => $issuer['pin'] ?? $billing->taxPin(),
                'tagline' => $issuer['tagline'] ?? $billing->issuerTagline(),
                'phone' => $issuer['phone'] ?? $billing->issuerPhone(),
                'email' => $issuer['email'] ?? $billing->issuerEmail(),
                'website' => $issuer['website'] ?? $billing->issuerWebsite(),
                'address' => $issuer['address'] ?? $billing->issuerAddress(),
                'footer_text' => $billing->invoiceFooterNotes(),
                'payment_instructions' => $billing->paymentInstructions(),
            ],
            'paymentOptions' => [
                'bank_name' => $billing->bankName(),
                'bank_account_name' => $billing->bankAccountName(),
                'bank_account_number' => $billing->bankAccountNumber(),
                'bank_branch' => $billing->bankBranch(),
                'mpesa_paybill' => $billing->mpesaPaybill(),
                'paybill_account_number' => $billing->paybillAccountNumber(),
            ],
            'initialPreviewHtml' => $initialPreviewHtml,
            'initialPreviewPaperSize' => $initialPreviewPaperSize,
        ]);
    }

    public function store(ManualFinancialDocumentRequest $request, ManualDocumentCreator $creator): RedirectResponse
    {
        try {
            $invoice = $creator->create($request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('invoices.create', ['type' => $request->input('document_type')])
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('invoices.preview', $invoice)
            ->with('status', __('Document :number created. Review the preview below.', ['number' => $invoice->invoice_number]));
    }

    public function previewManualDocument(
        ManualDocumentPreviewRequest $request,
        ManualDocumentPreviewBuilder $previewBuilder,
        DocumentRenderer $renderer,
        DocumentTemplateResolver $templateResolver,
    ): JsonResponse {
        $data = $request->validated();
        $templateId = $templateResolver->defaultTemplateId($data['document_type']);
        if (! $templateId) {
            abort(422, __('No active template configured for this document type.'));
        }

        $template = DocumentTemplate::query()
            ->whereKey($templateId)
            ->where('type', $data['document_type'])
            ->where('active', true)
            ->firstOrFail();

        $snapshot = $previewBuilder->build($data);
        $html = $renderer->render($template, $snapshot);

        return response()->json([
            'html' => $html,
            'paper_size' => $template->paper_size,
        ]);
    }

    public function tenantBillingProfile(Tenant $tenant): JsonResponse
    {
        $tenant->load(['projectSubscriptions.project']);

        return response()->json([
            'company_name' => $tenant->company_name,
            'billing_contact_name' => $tenant->billing_contact_name,
            'billing_email' => $tenant->billing_email,
            'billing_phone' => $tenant->billing_phone,
            'billing_address' => $tenant->billing_address,
            'currency' => $tenant->billing_preferred_currency ?? $tenant->tenant_currency ?? 'KES',
            'subscriptions' => $tenant->projectSubscriptions->map(fn ($s) => [
                'id' => $s->id,
                'label' => ($s->project?->name ?? __('Project')).' — '.($s->package_name ?? ''),
            ])->values(),
        ]);
    }

    public function convertProforma(TenantInvoice $invoice, ProformaConverter $converter): RedirectResponse
    {
        try {
            $newInvoice = $converter->convert($invoice);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('invoices.show', $newInvoice)
            ->with('status', __('Proforma converted to invoice.'));
    }

    public function generate(Request $request, RecurringBillingProcessor $recurring, DraftInvoiceGenerator $drafts): RedirectResponse
    {
        $scopeFilter = app(RbacScopeFilter::class);
        $recurringCount = $recurring->processDueSchedules()->count();
        $draftCount = 0;

        $tenants = $scopeFilter->isGlobalScope()
            ? Tenant::query()->where('status', 'active')->get()
            : $scopeFilter->applyTenantScope(Tenant::query())->where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $tenant->load(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'usageMetric']);
            if ($drafts->generate($tenant)) {
                $draftCount++;
            }
        }

        return redirect()
            ->route('invoices.index', ['tab' => 'invoices'])
            ->with('status', __('Generated :recurring recurring and :draft draft invoice(s).', [
                'recurring' => $recurringCount,
                'draft' => $draftCount,
            ]));
    }

    public function sendReminders(CollectionReminderService $reminders): RedirectResponse
    {
        $counts = $reminders->processAutomatedReminders();

        return redirect()
            ->route('invoices.index', ['tab' => 'collections'])
            ->with('status', __('Dispatched :count reminder(s). (:skipped skipped)', [
                'count' => $counts['reminders'],
                'skipped' => $counts['skipped'],
            ]));
    }

    public function sendInvoiceReminder(
        Request $request,
        TenantInvoice $invoice,
        CollectionReminderService $reminders,
    ): RedirectResponse {
        $request->validate(['recipient_email' => ['nullable', 'email', 'max:255']]);

        $result = $reminders->sendReminder($invoice, $request->input('recipient_email'));

        return $result['success']
            ? back()->with('status', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function storeCollectionNote(
        StoreCollectionNoteRequest $request,
        TenantInvoice $invoice,
        CollectionWorkflowService $workflow,
    ): RedirectResponse {
        $workflow->addNote($invoice, $request->validated());

        return back()->with('status', __('Collection note added.'));
    }

    public function completeCollectionFollowUp(
        TenantInvoice $invoice,
        CollectionNote $note,
        CollectionWorkflowService $workflow,
    ): RedirectResponse {
        if ((int) $note->tenant_invoice_id !== (int) $invoice->id) {
            abort(404);
        }

        $workflow->completeFollowUp($note);

        return back()->with('status', __('Follow-up marked complete.'));
    }

    public function markInvoiceDisputed(
        Request $request,
        TenantInvoice $invoice,
        CollectionWorkflowService $workflow,
    ): RedirectResponse {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $workflow->markDisputed($invoice, $data);

        return back()->with('status', __('Invoice marked as disputed.'));
    }

    public function recordPromiseToPay(
        PromiseToPayRequest $request,
        TenantInvoice $invoice,
        CollectionWorkflowService $workflow,
    ): RedirectResponse {
        $workflow->recordPromiseToPay($invoice, $request->validated());

        return back()->with('status', __('Promise to pay recorded.'));
    }

    public function escalateInvoice(
        Request $request,
        TenantInvoice $invoice,
        CollectionWorkflowService $workflow,
    ): RedirectResponse {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $workflow->escalate($invoice, $data);

        return back()->with('status', __('Invoice escalated for follow-up.'));
    }

    public function toggleSchedule(InvoiceRecurringSchedule $schedule): RedirectResponse
    {
        $schedule->update(['enabled' => ! $schedule->enabled]);

        return redirect()
            ->route('invoices.index', ['tab' => 'recurring'])
            ->with('status', $schedule->enabled
                ? __('Recurring schedule enabled.')
                : __('Recurring schedule paused.'));
    }

    public function updateAutomationRules(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reminder_after_days' => ['required', 'integer', 'min:1', 'max:90'],
            'penalty_after_days' => ['required', 'integer', 'min:1', 'max:180'],
            'suspension_after_days' => ['required', 'integer', 'min:1', 'max:365'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:90'],
            'penalty_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'recurring_enabled' => ['sometimes', 'boolean'],
            'auto_send_invoices' => ['sometimes', 'boolean'],
            'auto_send_receipts' => ['sometimes', 'boolean'],
            'auto_generate_pdf' => ['sometimes', 'boolean'],
        ]);

        $rules = BillingAutomationRule::platform();
        $rules->update([
            ...$data,
            'recurring_enabled' => $request->boolean('recurring_enabled'),
            'auto_send_invoices' => $request->boolean('auto_send_invoices'),
            'auto_send_receipts' => $request->boolean('auto_send_receipts'),
            'auto_generate_pdf' => $request->boolean('auto_generate_pdf'),
        ]);

        return redirect()
            ->route('invoices.index', ['tab' => 'automation'])
            ->with('status', __('Automation rules updated.'));
    }

    public function updateTemplate(Request $request, DocumentTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'footer_text' => ['nullable', 'string', 'max:2000'],
            'signature_label' => ['nullable', 'string', 'max:120'],
            'show_qr' => ['sometimes', 'boolean'],
            'watermark' => ['nullable', 'string', 'max:120'],
            'active' => ['sometimes', 'boolean'],
            'paper_size' => ['nullable', 'string', 'max:16'],
            'orientation' => ['nullable', 'string', 'max:16'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $branding = array_merge($template->branding ?? [], [
            'primary_color' => $data['primary_color'] ?? '#4f46e5',
            'accent_color' => $data['accent_color'] ?? '#f59e0b',
            'footer_text' => $data['footer_text'] ?? null,
            'signature_label' => $data['signature_label'] ?? null,
            'show_qr' => $request->boolean('show_qr'),
            'watermark' => $data['watermark'] ?? null,
        ]);

        $updates = [
            'name' => $data['name'],
            'branding' => $branding,
            'active' => $request->boolean('active', true),
        ];

        if (! empty($data['paper_size'])) {
            $updates['paper_size'] = strtoupper($data['paper_size']);
        }
        if (! empty($data['orientation'])) {
            $updates['orientation'] = strtolower($data['orientation']);
        }

        if ($request->boolean('is_default')) {
            DocumentTemplate::query()
                ->where('type', $template->type)
                ->whereKeyNot($template->id)
                ->update(['is_default' => false]);
            $updates['is_default'] = true;
        }

        $template->update($updates);

        return redirect()
            ->route('invoices.index', ['tab' => 'templates'])
            ->with('status', __('Template updated.'));
    }

    public function previewDocumentTemplate(DocumentTemplate $documentTemplate, DocumentRenderer $renderer): View
    {
        abort_unless($documentTemplate->active, 404);
        $html = $renderer->render($documentTemplate, SampleFinancialDocumentSnapshot::proforma());

        return view('admin.invoices.template-sample-preview', [
            'documentTemplate' => $documentTemplate,
            'previewHtml' => $html,
        ]);
    }

    public function approveQuotation(TenantInvoice $invoice, QuotationConverter $converter): RedirectResponse
    {
        $converter->approve($invoice);

        return back()->with('status', __('Quotation approved.'));
    }

    public function convertQuotation(TenantInvoice $invoice, QuotationConverter $converter): RedirectResponse
    {
        try {
            $newInvoice = $converter->convert($invoice);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('invoices.show', $newInvoice)
            ->with('status', __('Quotation converted to invoice.'));
    }

    public function show(TenantInvoice $invoice, BillingSettings $billingSettings): View
    {
        $invoice->load(['tenant', 'lineItems', 'payments', 'projectSubscription.project', 'generatedDocuments', 'collectionNotes', 'linkedInvoice', 'convertedInvoice']);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'billingSettings' => $billingSettings,
            'activityLogs' => $this->activityQuery->forContext(invoiceId: $invoice->id),
            'defaultRecipient' => $invoice->defaultRecipientEmail(),
            'persistedDocument' => $invoice->generatedDocuments()->latest('id')->first(),
        ]);
    }

    public function preview(
        TenantInvoice $invoice,
        DocumentRenderer $renderer,
        DocumentSnapshotBuilder $builder,
        DocumentFinalizer $finalizer,
    ): View {
        $invoice->load(['tenant', 'lineItems', 'projectSubscription.project', 'payments', 'generatedDocuments']);

        $template = $finalizer->resolveTemplate($invoice);
        $persistedDocument = $invoice->generatedDocuments()->latest('id')->first();

        $usePersistedSnapshot = $invoice->isFinalized()
            && $persistedDocument
            && filled($persistedDocument->html_snapshot);

        $previewHtml = $usePersistedSnapshot
            ? $persistedDocument->html_snapshot
            : $renderer->render($template, $builder->build($invoice));

        return view('admin.invoices.preview', [
            'invoice' => $invoice,
            'previewHtml' => $previewHtml,
            'documentTemplate' => $template,
            'persistedDocument' => $persistedDocument,
            'defaultRecipient' => $invoice->defaultRecipientEmail(),
        ]);
    }

    public function downloadPdf(TenantInvoice $invoice, DocumentDeliveryService $delivery): Response|RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $delivery->downloadPdfResponse($invoice);
    }

    public function emailDocument(
        SendFinancialDocumentRequest $request,
        TenantInvoice $invoice,
        DocumentDeliveryService $delivery,
    ): RedirectResponse {
        $result = $delivery->sendEmail(
            $invoice,
            $request->validated('recipient_email'),
            $request->boolean('resend'),
        );

        return $result['success']
            ? back()->with('status', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function finalizeDocument(TenantInvoice $invoice, DocumentDeliveryService $delivery): RedirectResponse
    {
        $delivery->ensurePdf($invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']));

        return back()->with('status', __('Document finalized and PDF prepared.'));
    }

    public function regeneratePdf(TenantInvoice $invoice, DocumentFinalizer $finalizer): RedirectResponse
    {
        if (! $invoice->canRegenerate()) {
            return back()->with('error', $invoice->regenerateBlockedReason() ?? __('This document cannot be regenerated.'));
        }

        try {
            $finalizer->regenerate($invoice->fresh());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = $invoice->wasEmailed()
            ? __('Document regenerated. A new revision was saved; previously emailed PDFs remain historical records.')
            : __('Document regenerated.');

        return back()->with('status', $message);
    }

    public function markSent(TenantInvoice $invoice, DocumentDeliveryService $delivery): RedirectResponse
    {
        try {
            $delivery->markSent(
                $invoice,
                BillingAutomationRule::platform()->auto_send_invoices
                    && ($invoice->document_type ?? 'invoice') === BillingDocumentType::INVOICE,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', __('Document marked as sent and finalized.'));
    }

    public function cancel(TenantInvoice $invoice): RedirectResponse
    {
        if (! $invoice->canCancel()) {
            if ($invoice->isPaid()) {
                return back()->with('error', __('Paid invoices cannot be cancelled.'));
            }

            if ($invoice->isFinalized()) {
                return back()->with('error', __('Finalized documents cannot be cancelled. Create a credit note instead.'));
            }

            return back()->with('error', __('This document cannot be cancelled.'));
        }

        $old = ['status' => $invoice->status];
        $invoice->update(['status' => 'cancelled']);

        $this->activityLogger->log(
            'document.cancelled',
            ActivityLogCategory::BILLING,
            __(':type :number cancelled', [
                'type' => $invoice->documentTypeLabel(),
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            $old,
            ['status' => 'cancelled'],
        );

        return back()->with('status', __('Invoice cancelled.'));
    }

    public function recordPayment(RecordPaymentRequest $request, TenantInvoice $invoice, PaymentRecorderService $recorder): RedirectResponse
    {
        if (! $invoice->canRecordPayment()) {
            return back()->with('error', __('Cannot record payment on this invoice.'));
        }

        $data = $request->validated();
        $data['tenant_invoice_id'] = $invoice->id;

        $payment = $recorder->recordForInvoice($invoice, $data);

        return back()->with('status', __('Payment recorded. Balance updated.'));
    }

    public function markPaid(TenantInvoice $invoice): RedirectResponse
    {
        if ($invoice->balanceDue() > 0.009) {
            return back()->with('error', __('Invoice still has an outstanding balance.'));
        }

        $old = ['status' => $invoice->status];
        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->invoiceTotal() + (float) $invoice->penalty_amount,
        ]);

        $this->activityLogger->log(
            'invoice.marked_paid',
            ActivityLogCategory::BILLING,
            __('Invoice :number marked paid', ['number' => $invoice->invoice_number]),
            $invoice,
            $old,
            ['status' => 'paid'],
        );

        return back()->with('status', __('Invoice marked as paid.'));
    }
}
