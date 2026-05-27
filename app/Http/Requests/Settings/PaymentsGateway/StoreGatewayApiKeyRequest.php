<?php

namespace App\Http\Requests\Settings\PaymentsGateway;

use Illuminate\Foundation\Http\FormRequest;

class StoreGatewayApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:100'],
            'allowed_ips' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /** @return array<string, mixed> */
    public function gatewayPayload(): array
    {
        $payload = $this->validated();
        $ips = trim((string) ($payload['allowed_ips'] ?? ''));

        if ($ips !== '') {
            $payload['allowed_ips'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $ips) ?: [])));
        } else {
            unset($payload['allowed_ips']);
        }

        return $payload;
    }
}
