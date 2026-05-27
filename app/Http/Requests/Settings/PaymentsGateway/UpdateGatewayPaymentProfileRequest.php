<?php

namespace App\Http\Requests\Settings\PaymentsGateway;

use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGatewayPaymentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('default_collection_account_id');
        $this->request->remove('default_disbursement_account_id');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'environment' => ['nullable', Rule::in(GatewayFormOptions::paymentEnvironments())],
            'status' => ['nullable', Rule::in(GatewayFormOptions::paymentProfileStatuses())],
            'default_collection_account_uuid' => ['nullable', 'uuid'],
            'default_disbursement_account_uuid' => ['nullable', 'uuid'],
            'tenant_webhook_url' => ['nullable', 'url', 'max:2048'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}
