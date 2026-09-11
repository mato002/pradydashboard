<?php

namespace App\Http\Requests;

use App\Domain\Billing\ManualDocumentCreator;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\PaymentSource;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualFinancialDocumentRequest extends FormRequest
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
        $type = $this->input('document_type', BillingDocumentType::INVOICE);
        $isReceipt = $type === BillingDocumentType::RECEIPT;
        $linked = $this->filled('linked_invoice_id');

        $rules = [
            'document_type' => ['required', Rule::in([
                BillingDocumentType::INVOICE,
                BillingDocumentType::PROFORMA,
                BillingDocumentType::QUOTATION,
                BillingDocumentType::RECEIPT,
            ])],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'tenant_project_subscription_id' => ['nullable', 'integer', 'exists:tenant_project_subscriptions,id'],
            'manual_client_name' => ['nullable', 'string', 'max:255', 'required_without:tenant_id'],
            'manual_client_email' => ['nullable', 'email', 'max:255'],
            'manual_client_phone' => ['nullable', 'string', 'max:80'],
            'manual_client_address' => ['nullable', 'string', 'max:2000'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'linked_invoice_id' => [
                'nullable',
                'integer',
                'exists:tenant_invoices,id',
                $this->linkedInvoiceBelongsToTenantRule(),
            ],
            'amount_received' => ['nullable', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', Rule::in(PaymentSource::all())],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_date' => ['nullable', 'date'],
            'line_description' => ['nullable', 'string', 'max:500'],
            'line_items' => [Rule::requiredIf(! $isReceipt || ! $linked), 'array', 'min:1'],
            'line_items.*.description' => ['required_with:line_items', 'string', 'max:500'],
            'line_items.*.quantity' => ['required_with:line_items', 'numeric', 'min:0.01'],
            'line_items.*.unit_price' => ['required_with:line_items', 'numeric', 'min:0.01'],
            'line_items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.item_type' => ['nullable', Rule::in(ManualDocumentCreator::LINE_ITEM_TYPES)],
        ];

        if ($isReceipt && $linked) {
            $rules['amount_received'] = ['required', 'numeric', 'min:0.01'];
            $rules['payment_method'] = ['required', 'string', Rule::in(PaymentSource::all())];
            $rules['payment_date'] = ['required', 'date'];
            $rules['line_items'] = ['nullable', 'array'];
        }

        if ($isReceipt && ! $linked) {
            $rules['amount_received'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['payment_method'] = ['required', 'string', Rule::in(PaymentSource::all())];
            $rules['payment_date'] = ['required', 'date'];
            $rules['line_items'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('document_type', BillingDocumentType::INVOICE);
            $isReceipt = $type === BillingDocumentType::RECEIPT;
            $linked = $this->filled('linked_invoice_id');

            if ($isReceipt && $linked) {
                return;
            }

            $lines = $this->input('line_items', []);
            if (! is_array($lines) || $lines === []) {
                return;
            }

            $hasPositiveLine = false;
            foreach ($lines as $index => $line) {
                if (! is_array($line)) {
                    continue;
                }
                $qty = (float) ($line['quantity'] ?? 0);
                $price = (float) ($line['unit_price'] ?? 0);
                $discount = (float) ($line['discount'] ?? 0);
                $lineTotal = max(0, ($qty * $price) - $discount);
                if ($lineTotal <= 0.009) {
                    $validator->errors()->add(
                        "line_items.{$index}.unit_price",
                        __('Each line item must have a positive amount (quantity × unit price).'),
                    );
                } else {
                    $hasPositiveLine = true;
                }
            }

            if (! $hasPositiveLine && ! $validator->errors()->has('line_items')) {
                $validator->errors()->add(
                    'line_items',
                    __('Document total must be greater than zero.'),
                );
            }
        });
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
