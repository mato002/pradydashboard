<?php

namespace App\Domain\Billing;

use App\Models\DocumentTemplate;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\ActivityLogCategory;
use App\Domain\Activity\ActivityLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatementGenerator
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly StatementDataBuilder $statementDataBuilder,
        private readonly DocumentFinalizer $documentFinalizer,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function generate(Tenant $tenant, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): TenantInvoice
    {
        $periodStart ??= now()->startOfMonth();
        $periodEnd ??= now()->endOfMonth();

        return DB::transaction(function () use ($tenant, $periodStart, $periodEnd): TenantInvoice {
            $statementData = $this->statementDataBuilder->build($tenant, $periodStart, $periodEnd);
            $closingBalance = (float) $statementData['closing_balance'];

            $templateId = DocumentTemplate::query()
                ->where('type', BillingDocumentType::STATEMENT)
                ->where('active', true)
                ->orderByDesc('is_default')
                ->value('id');

            $statement = TenantInvoice::query()->create([
                'tenant_id' => $tenant->id,
                'invoice_number' => $this->numberGenerator->next(BillingDocumentType::STATEMENT),
                'document_type' => BillingDocumentType::STATEMENT,
                'currency' => $tenant->billing_preferred_currency ?? $tenant->tenant_currency ?? 'KES',
                'subtotal' => abs($closingBalance),
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => abs($closingBalance),
                'amount_due' => max(0, $closingBalance),
                'amount_paid' => $closingBalance < 0 ? abs($closingBalance) : 0,
                'status' => 'sent',
                'issue_date' => $periodEnd->toDateString(),
                'issued_at' => now(),
                'due_date' => $periodEnd->toDateString(),
                'statement_period_start' => $periodStart->toDateString(),
                'statement_period_end' => $periodEnd->toDateString(),
                'notes' => __('Account statement :start — :end', [
                    'start' => $periodStart->toDateString(),
                    'end' => $periodEnd->toDateString(),
                ]),
                'document_template_id' => $templateId,
                'created_source' => 'automatic',
                'generated_by' => auth()->user()?->email ?? 'billing:statement',
            ]);

            $this->documentFinalizer->finalize($statement->fresh(['tenant']));

            $this->activityLogger->log(
                'statement.generated',
                ActivityLogCategory::BILLING,
                __('Account statement :number generated for :start — :end', [
                    'number' => $statement->invoice_number,
                    'start' => $periodStart->toDateString(),
                    'end' => $periodEnd->toDateString(),
                ]),
                $statement,
                null,
                [
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'closing_balance' => $closingBalance,
                ],
            );

            return $statement->fresh(['tenant', 'generatedDocuments']);
        });
    }
}
