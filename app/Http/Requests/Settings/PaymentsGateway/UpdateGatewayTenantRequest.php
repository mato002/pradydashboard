<?php

namespace App\Http\Requests\Settings\PaymentsGateway;

use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGatewayTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'external_tenant_id' => ['nullable', 'string', 'max:255'],
            'project_code' => ['nullable', 'string', 'max:255'],
            'system_type' => ['nullable', Rule::in(GatewayFormOptions::systemTypes())],
            'primary_domain' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(GatewayFormOptions::tenantStatuses())],
        ];
    }
}
