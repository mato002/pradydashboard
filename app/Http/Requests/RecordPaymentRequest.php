<?php

namespace App\Http\Requests;

use App\Support\Billing\PaymentSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
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
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'tenant_invoice_id' => ['nullable', 'integer', 'exists:tenant_invoices,id'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'payer_phone' => ['nullable', 'string', 'max:40'],
            'payer_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:8'],
            'payment_date' => ['required', 'date'],
            'source' => ['required', Rule::in(PaymentSource::all())],
            'method' => ['nullable', 'string', 'max:80'],
            'reference' => ['nullable', 'string', 'max:120'],
            'bank_source' => ['nullable', 'string', 'max:120'],
            'narration' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'transaction_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}
