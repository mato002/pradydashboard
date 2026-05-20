<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Billing\BillingSettings;
use App\Domain\Billing\DocumentFinalizer;
use App\Domain\Billing\DraftInvoiceGenerator;
use App\Domain\Billing\FinancialOperationsQuery;
use App\Domain\Billing\InvoiceEmailDelivery;
use App\Domain\Billing\InvoicePaymentRecorder;
use App\Domain\Billing\OverdueBillingProcessor;
use App\Domain\Billing\QuotationConverter;
use App\Domain\Billing\RecurringBillingProcessor;
use App\Domain\Rbac\RbacScopeFilter;
use App\Http\Controllers\Controller;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\InvoiceRecurringSchedule;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
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
            'collectionNotes' => $this->operations->recentCollectionNotes(),
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
            $data['overdueInvoices'] = $this->operations->collectionsData($scopeFilter);
        }

        return view('admin.invoices.index', $data);
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

    public function sendReminders(OverdueBillingProcessor $processor): RedirectResponse
    {
        $counts = $processor->process();

        return redirect()
            ->route('invoices.index', ['tab' => 'collections'])
            ->with('status', __('Dispatched :count reminder(s).', ['count' => $counts['reminders']]));
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
        ]);

        $branding = array_merge($template->branding ?? [], [
            'primary_color' => $data['primary_color'] ?? '#4f46e5',
            'accent_color' => $data['accent_color'] ?? '#f59e0b',
            'footer_text' => $data['footer_text'] ?? null,
            'signature_label' => $data['signature_label'] ?? null,
            'show_qr' => $request->boolean('show_qr'),
            'watermark' => $data['watermark'] ?? null,
        ]);

        $template->update([
            'name' => $data['name'],
            'branding' => $branding,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()
            ->route('invoices.index', ['tab' => 'templates'])
            ->with('status', __('Template updated.'));
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
        $invoice->load(['tenant', 'lineItems', 'payments', 'projectSubscription.project', 'generatedDocuments']);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'billingSettings' => $billingSettings,
            'activityLogs' => $this->activityQuery->forContext(invoiceId: $invoice->id),
        ]);
    }

    public function preview(TenantInvoice $invoice, DocumentFinalizer $finalizer): View
    {
        $document = $invoice->isFinalized()
            ? $invoice->generatedDocuments()->latest('id')->first()
            : $finalizer->finalize($invoice);

        return view('admin.invoices.preview', [
            'invoice' => $invoice,
            'document' => $document,
        ]);
    }

    public function downloadPdf(TenantInvoice $invoice, DocumentFinalizer $finalizer): Response|RedirectResponse
    {
        $document = $invoice->generatedDocuments()->latest('id')->first()
            ?? $finalizer->finalize($invoice);

        if (! $document->pdf_path || ! Storage::disk('local')->exists($document->pdf_path)) {
            return back()->with('error', __('PDF not available. Install dompdf/dompdf and regenerate.'));
        }

        return response()->download(
            Storage::disk('local')->path($document->pdf_path),
            $invoice->invoice_number.'.pdf',
        );
    }

    public function emailDocument(TenantInvoice $invoice, DocumentFinalizer $finalizer, InvoiceEmailDelivery $delivery): RedirectResponse
    {
        $document = $invoice->generatedDocuments()->latest('id')->first()
            ?? $finalizer->finalize($invoice);

        $delivery->send($invoice, $document)
            ? back()->with('status', __('Document emailed.'))
            : back()->with('error', __('Email delivery failed. Check billing email.'));
    }

    public function regeneratePdf(TenantInvoice $invoice, DocumentFinalizer $finalizer): RedirectResponse
    {
        $finalizer->regenerate($invoice);

        return back()->with('status', __('Document regenerated.'));
    }

    public function markSent(TenantInvoice $invoice, DocumentFinalizer $finalizer, InvoiceEmailDelivery $delivery): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', __('Only draft invoices can be marked as sent.'));
        }

        $old = ['status' => $invoice->status];
        $invoice->update([
            'status' => 'sent',
            'issued_at' => $invoice->issued_at ?? now(),
            'issue_date' => $invoice->issue_date ?? now()->toDateString(),
        ]);

        $document = $finalizer->finalize($invoice);

        if (BillingAutomationRule::platform()->auto_send_invoices) {
            $delivery->send($invoice, $document);
        }

        $this->activityLogger->log(
            'invoice.marked_sent',
            ActivityLogCategory::BILLING,
            __('Invoice :number marked sent', ['number' => $invoice->invoice_number]),
            $invoice,
            $old,
            ['status' => 'sent'],
        );

        return back()->with('status', __('Invoice marked as sent.'));
    }

    public function cancel(TenantInvoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', __('Paid invoices cannot be cancelled.'));
        }

        if ($invoice->isFinalized()) {
            return back()->with('error', __('Finalized documents cannot be cancelled. Create a credit note instead.'));
        }

        $old = ['status' => $invoice->status];
        $invoice->update(['status' => 'cancelled']);

        $this->activityLogger->log(
            'invoice.cancelled',
            ActivityLogCategory::BILLING,
            __('Invoice :number cancelled', ['number' => $invoice->invoice_number]),
            $invoice,
            $old,
            ['status' => 'cancelled'],
        );

        return back()->with('status', __('Invoice cancelled.'));
    }

    public function recordPayment(Request $request, TenantInvoice $invoice, InvoicePaymentRecorder $recorder): RedirectResponse
    {
        if (in_array($invoice->status, ['cancelled', 'void', 'paid'], true)) {
            return back()->with('error', __('Cannot record payment on this invoice.'));
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'string', 'max:80'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $recorder->record($invoice, [
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            'payment.recorded',
            ActivityLogCategory::BILLING,
            __('Payment of :amount recorded on invoice :number', [
                'amount' => $data['amount'],
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            null,
            $data,
        );

        return back()->with('status', __('Payment recorded.'));
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
