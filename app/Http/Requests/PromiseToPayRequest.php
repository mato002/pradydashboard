<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromiseToPayRequest extends FormRequest
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
            'note' => ['nullable', 'string', 'max:5000'],
            'promise_to_pay_date' => ['required', 'date', 'after_or_equal:today'],
            'promised_amount' => ['nullable', 'numeric', 'min:0.01'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}
