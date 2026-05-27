<?php

namespace App\Http\Requests\Settings\PaymentsGateway;

use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGatewayPaybillAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_name' => ['required', 'string', 'max:255'],
            'account_code' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(GatewayFormOptions::paybillAccountTypes())],
            'shortcode' => ['required', 'string', 'max:20'],
            'stk_shortcode' => ['nullable', 'string', 'max:20'],
            'environment' => ['nullable', Rule::in(GatewayFormOptions::paymentEnvironments())],
            'purpose' => ['nullable', 'string', 'max:255'],
            'branch_code' => ['nullable', 'string', 'max:50'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'supports_stk' => ['nullable', 'boolean'],
            'supports_c2b' => ['nullable', 'boolean'],
            'supports_b2c' => ['nullable', 'boolean'],
            'supports_b2b' => ['nullable', 'boolean'],
            'supports_reversal' => ['nullable', 'boolean'],
            'consumer_key' => ['nullable', 'string', 'max:255'],
            'consumer_secret' => ['nullable', 'string', 'max:255'],
            'passkey' => ['nullable', 'string', 'max:255'],
            'initiator_name' => ['nullable', 'string', 'max:255'],
            'security_credential' => ['nullable', 'string'],
            'validation_url' => ['nullable', 'url', 'max:2048'],
            'confirmation_url' => ['nullable', 'url', 'max:2048'],
            'stk_callback_url' => ['nullable', 'url', 'max:2048'],
            'b2c_result_url' => ['nullable', 'url', 'max:2048'],
            'b2c_timeout_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['nullable', Rule::in(GatewayFormOptions::paybillAccountStatuses())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'supports_stk' => $this->boolean('supports_stk'),
            'supports_c2b' => $this->boolean('supports_c2b'),
            'supports_b2c' => $this->boolean('supports_b2c'),
            'supports_b2b' => $this->boolean('supports_b2b'),
            'supports_reversal' => $this->boolean('supports_reversal'),
        ]);
    }
}
