<?php

namespace App\Http\Requests;

use App\Support\Billing\CollectionNoteOutcome;
use App\Support\Billing\CollectionNoteStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollectionNoteRequest extends FormRequest
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
            'note' => ['required', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date'],
            'promise_to_pay_date' => ['nullable', 'date'],
            'promised_amount' => ['nullable', 'numeric', 'min:0'],
            'outcome' => ['nullable', Rule::in(CollectionNoteOutcome::all())],
            'status' => ['nullable', Rule::in(CollectionNoteStatus::all())],
        ];
    }
}
