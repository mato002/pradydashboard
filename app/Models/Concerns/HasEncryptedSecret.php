<?php

namespace App\Models\Concerns;

use App\Domain\Servers\Support\ServerConnectionConfig;
use Illuminate\Support\Facades\Crypt;

trait HasEncryptedSecret
{
    public function decryptedApiSecret(): ?string
    {
        $stored = $this->attributes['api_secret'] ?? null;
        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        if ($value === null || $value === '' || $value === ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER) {
            return;
        }

        $this->attributes['api_secret'] = Crypt::encryptString($value);
    }
}
