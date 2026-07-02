<?php

namespace App\Support\Phone;

final class EastAfricaPhone
{
    /**
     * @return list<array{iso: string, name: string, dial: string, local_min: int, local_max: int}>
     */
    public static function countries(): array
    {
        return config('east_africa_phone', []);
    }

    /**
     * @return list<string>
     */
    public static function dialCodes(): array
    {
        return array_map(fn (array $c) => $c['dial'], self::countries());
    }

    public static function dialForIso(?string $iso): string
    {
        $iso = strtoupper(trim((string) $iso));

        foreach (self::countries() as $country) {
            if ($country['iso'] === $iso) {
                return $country['dial'];
            }
        }

        return '254';
    }

    /**
     * @return array{dial_code: string, local: string}
     */
    public static function parse(?string $phone, ?string $defaultDial = '254'): array
    {
        if ($phone === null || trim($phone) === '') {
            return ['dial_code' => $defaultDial, 'local' => ''];
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return ['dial_code' => $defaultDial, 'local' => ''];
        }

        $sorted = self::countries();
        usort($sorted, fn (array $a, array $b) => strlen($b['dial']) <=> strlen($a['dial']));

        foreach ($sorted as $country) {
            $dial = $country['dial'];
            if (str_starts_with($digits, $dial)) {
                return [
                    'dial_code' => $dial,
                    'local' => substr($digits, strlen($dial)),
                ];
            }
        }

        if (str_starts_with($digits, '0')) {
            return ['dial_code' => $defaultDial, 'local' => ltrim($digits, '0')];
        }

        return ['dial_code' => $defaultDial, 'local' => $digits];
    }

    public static function compose(?string $dialCode, ?string $local): ?string
    {
        $localDigits = self::sanitizeLocal($local);

        if ($localDigits === '') {
            return null;
        }

        $dial = preg_replace('/\D+/', '', (string) $dialCode) ?? '';
        $country = self::countryForDial($dial);

        if ($country === null) {
            return null;
        }

        if (! self::localLengthValid($localDigits, $country)) {
            return null;
        }

        return '+'.$dial.$localDigits;
    }

    public static function isValid(?string $dialCode, ?string $local): bool
    {
        return self::compose($dialCode, $local) !== null;
    }

    public static function sanitizeLocal(?string $local): string
    {
        $digits = preg_replace('/\D+/', '', (string) $local) ?? '';

        return ltrim($digits, '0');
    }

    /**
     * @return array{iso: string, name: string, dial: string, local_min: int, local_max: int}|null
     */
    public static function countryForDial(string $dial): ?array
    {
        $dial = preg_replace('/\D+/', '', $dial) ?? '';

        foreach (self::countries() as $country) {
            if ($country['dial'] === $dial) {
                return $country;
            }
        }

        return null;
    }

    /**
     * @param  array{iso: string, name: string, dial: string, local_min: int, local_max: int}  $country
     */
    public static function localLengthValid(string $localDigits, array $country): bool
    {
        $len = strlen($localDigits);

        return $len >= $country['local_min'] && $len <= $country['local_max'];
    }

    public static function validationMessage(?string $dialCode): string
    {
        $country = self::countryForDial((string) $dialCode);

        if ($country === null) {
            return __('Select a valid country code.');
        }

        if ($country['local_min'] === $country['local_max']) {
            return __('Enter :digits digits for :country (without the leading 0).', [
                'digits' => $country['local_min'],
                'country' => $country['name'],
            ]);
        }

        return __('Enter :min–:max digits for :country (without the leading 0).', [
            'min' => $country['local_min'],
            'max' => $country['local_max'],
            'country' => $country['name'],
        ]);
    }
}
