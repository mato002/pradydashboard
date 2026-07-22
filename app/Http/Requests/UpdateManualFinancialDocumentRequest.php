<?php

namespace App\Http\Requests;

use App\Models\TenantInvoice;

class UpdateManualFinancialDocumentRequest extends ManualFinancialDocumentRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof TenantInvoice && $invoice->canEdit();
    }

    protected function prepareForValidation(): void
    {
        $invoice = $this->route('invoice');
        if ($invoice instanceof TenantInvoice) {
            $this->merge(['document_type' => $invoice->document_type]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['document_type']);

        return $rules;
    }
}
