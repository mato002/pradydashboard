<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendFinancialDocumentRequest extends FormRequest
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
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'resend' => ['nullable', 'boolean'],
        ];
    }
}
