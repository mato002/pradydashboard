<?php

namespace App\Http\Requests;

use App\Domain\Billing\ManualDocumentCreator;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualDocumentPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in([
                BillingDocumentType::INVOICE,
                BillingDocumentType::PROFORMA,
                BillingDocumentType::QUOTATION,
                BillingDocumentType::RECEIPT,
            ])],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'tenant_project_subscription_id' => ['nullable', 'integer', 'exists:tenant_project_subscriptions,id'],
            'manual_client_name' => ['nullable', 'string', 'max:255'],
            'manual_client_email' => ['nullable', 'email', 'max:255'],
            'manual_client_phone' => ['nullable', 'string', 'max:80'],
            'manual_client_address' => ['nullable', 'string', 'max:2000'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'linked_invoice_id' => [
                'nullable',
                'integer',
                'exists:tenant_invoices,id',
                $this->linkedInvoiceBelongsToTenantRule(),
            ],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'line_description' => ['nullable', 'string', 'max:500'],
            'line_items' => ['nullable', 'array'],
            'line_items.*.description' => ['nullable', 'string', 'max:500'],
            'line_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.item_type' => ['nullable', Rule::in(ManualDocumentCreator::LINE_ITEM_TYPES)],
        ];
    }

    private function linkedInvoiceBelongsToTenantRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value || ! $this->filled('tenant_id')) {
                return;
            }

            $invoice = TenantInvoice::query()->find((int) $value);
            if (! $invoice) {
                return;
            }

            if ((int) $invoice->tenant_id !== (int) $this->input('tenant_id')) {
                $fail(__('The selected invoice does not belong to this tenant.'));
            }
        };
    }
}
