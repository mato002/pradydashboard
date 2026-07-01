<?php

namespace App\Support\Billing;

class PradyClassicBrandAssets
{
    public static function headerPath(): string
    {
        $configured = config('billing.document_brand_header_path');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return public_path('images/prady-documents/prady-header.png');
    }

    public static function footerPath(): string
    {
        $configured = config('billing.document_brand_footer_path');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return public_path('images/prady-documents/prady-footer-wave.png');
    }

    public static function headerExists(): bool
    {
        return is_file(self::headerPath());
    }

    public static function footerExists(): bool
    {
        return is_file(self::footerPath());
    }

    /**
     * DomPDF-safe image src (base64 data URI from local public asset).
     */
    public static function headerSrc(): ?string
    {
        return self::imageSrc(self::headerPath());
    }

    /**
     * DomPDF-safe image src (base64 data URI from local public asset).
     */
    public static function footerSrc(): ?string
    {
        return self::imageSrc(self::footerPath());
    }

    private static function imageSrc(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
