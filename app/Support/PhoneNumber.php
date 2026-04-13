<?php

namespace App\Support;

final class PhoneNumber
{
    public static function normalize(string $phone): string
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($normalizedPhone, '62')) {
            return '0'.substr($normalizedPhone, 2);
        }

        if (str_starts_with($normalizedPhone, '8')) {
            return '0'.$normalizedPhone;
        }

        return $normalizedPhone;
    }

    public static function isValidIndonesianMobile(string $phone): bool
    {
        return preg_match('/^08[1-9][0-9]{6,11}$/', self::normalize($phone)) === 1;
    }

    /**
     * @return array<int, string>
     */
    public static function variants(string $normalizedPhone): array
    {
        $localPhone = ltrim($normalizedPhone, '0');

        return array_values(array_filter(array_unique([
            $normalizedPhone,
            $localPhone !== '' ? '62'.$localPhone : null,
            $localPhone !== '' ? '+62'.$localPhone : null,
        ])));
    }

    public static function resolveEmail(string $email, string $normalizedPhone): string
    {
        $trimmedEmail = strtolower(trim($email));

        if ($trimmedEmail !== '') {
            return $trimmedEmail;
        }

        return $normalizedPhone.'@phone.lazalazhar5.local';
    }
}
