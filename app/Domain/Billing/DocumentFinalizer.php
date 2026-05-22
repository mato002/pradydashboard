<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\BillingAutomationRule;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentFinalizer
{
    public function __construct(
        private readonly DocumentSnapshotBuilder $snapshotBuilder,
        private readonly DocumentRenderer $renderer,
        private readonly PdfGenerator $pdfGenerator,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function finalize(
        TenantInvoice $invoice,
        ?DocumentTemplate $template = null,
        ?string $renderedBy = null,
        bool $forceNew = false,
    ): GeneratedDocument {
        if (! $forceNew && $invoice->finalized_at) {
            $existing = GeneratedDocument::query()
                ->where('tenant_invoice_id', $invoice->id)
                ->where('type', $invoice->document_type ?? 'invoice')
                ->latest('id')
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($invoice, $template, $renderedBy): GeneratedDocument {
            $template ??= $this->resolveTemplate($invoice);
            $snapshot = $this->snapshotBuilder->build($invoice);
            $html = $this->renderer->render($template, $snapshot);

            $pdfPath = null;
            if (BillingAutomationRule::platform()->auto_generate_pdf) {
                $pdfPath = $this->storePdf($invoice, $html, $template);
            }

            $actor = $renderedBy ?? Auth::user()?->email ?? 'system';

            $document = GeneratedDocument::query()->create([
                'tenant_id' => $invoice->tenant_id,
                'tenant_invoice_id' => $invoice->id,
                'type' => $invoice->document_type ?? 'invoice',
                'document_template_id' => $template->id,
                'html_snapshot' => $html,
                'data_snapshot' => $snapshot,
                'pdf_path' => $pdfPath,
                'rendered_at' => now(),
                'rendered_by' => $actor,
                'delivery_status' => 'not_sent',
            ]);

            $invoice->update([
                'finalized_at' => now(),
                'pdf_generated' => $pdfPath !== null,
                'revision_number' => (int) $invoice->revision_number,
                'delivery_status' => $invoice->delivery_status && $invoice->delivery_status !== 'pending'
                    ? $invoice->delivery_status
                    : 'not_sent',
            ]);

            $this->activityLogger->log(
                'document.finalized',
                ActivityLogCategory::BILLING,
                __(':type :number finalized', [
                    'type' => $invoice->document_type ?? 'invoice',
                    'number' => $invoice->invoice_number,
                ]),
                $invoice,
                null,
                ['generated_document_id' => $document->id],
            );

            return $document;
        });
    }

    public function regenerate(TenantInvoice $invoice, ?DocumentTemplate $template = null): GeneratedDocument
    {
        $invoice->increment('revision_number');

        return $this->finalize($invoice, $template, null, true);
    }

    public function ensurePdf(GeneratedDocument $document, TenantInvoice $invoice): GeneratedDocument
    {
        if ($document->pdf_path && Storage::disk('local')->exists($document->pdf_path)) {
            return $document;
        }

        $template = $document->document_template_id
            ? DocumentTemplate::query()->find($document->document_template_id)
            : null;
        $template ??= $this->resolveTemplate($invoice);

        $html = $document->html_snapshot ?: $this->renderer->render(
            $template,
            $this->snapshotBuilder->build($invoice),
        );

        $pdfPath = $this->storePdf($invoice, $html, $template);
        $document->update([
            'pdf_path' => $pdfPath,
            'html_snapshot' => $html,
        ]);
        $invoice->update(['pdf_generated' => $pdfPath !== null]);

        return $document->fresh();
    }

    private function storePdf(TenantInvoice $invoice, string $html, DocumentTemplate $template): ?string
    {
        $folder = $invoice->tenant_id ? (string) $invoice->tenant_id : 'manual';

        return $this->pdfGenerator->store(
            $html,
            sprintf(
                'billing/%s/%s-%s.pdf',
                $folder,
                $invoice->document_type ?? 'invoice',
                $invoice->invoice_number,
            ),
            $template->paper_size,
            $template->orientation,
        );
    }

    public function resolveTemplate(TenantInvoice $invoice): DocumentTemplate
    {
        if ($invoice->document_template_id) {
            $selected = DocumentTemplate::query()
                ->whereKey($invoice->document_template_id)
                ->where('active', true)
                ->first();
            if ($selected) {
                return $selected;
            }
        }

        $type = $invoice->document_type ?? 'invoice';

        return DocumentTemplate::query()
            ->where('type', $type)
            ->where('active', true)
            ->orderByDesc('is_default')
            ->first()
            ?? DocumentTemplate::query()
                ->where('type', 'invoice')
                ->where('active', true)
                ->orderByDesc('is_default')
                ->firstOrFail();
    }
}
