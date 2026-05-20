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
    ): GeneratedDocument {
        if ($invoice->finalized_at) {
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
                $filename = sprintf(
                    'billing/%d/%s-%s.pdf',
                    $invoice->tenant_id,
                    $invoice->document_type ?? 'invoice',
                    $invoice->invoice_number,
                );
                $pdfPath = $this->pdfGenerator->store(
                    $html,
                    $filename,
                    $template->paper_size,
                    $template->orientation,
                );
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
                'delivery_status' => 'pending',
            ]);

            $invoice->update([
                'finalized_at' => now(),
                'pdf_generated' => $pdfPath !== null,
                'revision_number' => (int) $invoice->revision_number,
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

        return $this->finalize($invoice, $template);
    }

    private function resolveTemplate(TenantInvoice $invoice): DocumentTemplate
    {
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
