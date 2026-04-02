<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(?string $phoneNumber, ?string $countryKey = null): string
    {
        $phone = trim((string) $phoneNumber);
        $key = trim((string) $countryKey);

        if ($phone === '') {
            return '';
        }

        // If full international number already provided, keep it normalized.
        if (str_starts_with($phone, '+') || str_starts_with($phone, '00')) {
            return self::normalizeInternational($phone);
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // Drop leading trunk zero for local input (e.g. 05xxxx -> 5xxxx).
        $digits = ltrim($digits, '0');

        if ($key === '') {
            return $digits;
        }

        $normalizedKey = self::normalizeKey($key);

        return $normalizedKey . $digits;
    }

    /**
     * Split a normalized phone number into country_key and local number.
     *
     * @return array{country_key: string|null, phone_number: string}
     */
    public static function split(?string $phoneNumber): array
    {
        $normalized = self::normalize($phoneNumber, null);

        if ($normalized === '') {
            return [
                'country_key' => null,
                'phone_number' => '',
            ];
        }

        if (! str_starts_with($normalized, '+')) {
            return [
                'country_key' => null,
                'phone_number' => $normalized,
            ];
        }

        $digits = substr($normalized, 1);

        // Generic split for any international prefix:
        // Try country code lengths from 4 down to 1 and keep the first valid split.
        // (E.164 country codes are usually up to 3 digits, but 4 keeps this flexible.)
        for ($len = 2; $len >= 1; $len--) {
            if (strlen($digits) <= $len) {
                continue;
            }

            $key = substr($digits, 0, $len);
            $local = substr($digits, $len);

            if ($local !== '') {
                return [
                    'country_key' => '+' . $key,
                    'phone_number' => $local,
                ];
            }
        }

        return [
            'country_key' => null,
            'phone_number' => $digits,
        ];
    }

    private static function normalizeInternational(string $number): string
    {
        $value = trim($number);

        if (str_starts_with($value, '00')) {
            $value = '+' . substr($value, 2);
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits !== '' ? '+' . $digits : '';
    }

    private static function normalizeKey(string $key): string
    {
        $digits = preg_replace('/\D+/', '', $key) ?? '';

        return '+' . $digits;
    }
}

