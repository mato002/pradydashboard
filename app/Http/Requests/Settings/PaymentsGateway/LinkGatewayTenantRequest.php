<?php

namespace App\Http\Requests\Settings\PaymentsGateway;

use Illuminate\Foundation\Http\FormRequest;

class LinkGatewayTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gateway_tenant_uuid' => ['nullable', 'uuid'],
        ];
    }
}
