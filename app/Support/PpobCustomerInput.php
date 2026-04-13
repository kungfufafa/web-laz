<?php

namespace App\Support;

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use Illuminate\Support\Str;

final class PpobCustomerInput
{
    private const TYPE_MOBILE = 'mobile';

    private const TYPE_NUMERIC = 'numeric';

    private const TYPE_SAFE_IDENTIFIER = 'safe_identifier';

    public static function normalize(string $customerNo, PpobProduct $product): string
    {
        $profile = self::profile($product);
        $trimmed = trim(preg_replace('/\s+/', ' ', $customerNo) ?? '');

        return match ($profile['type']) {
            self::TYPE_MOBILE => PhoneNumber::normalize($trimmed),
            self::TYPE_NUMERIC => preg_replace('/\D+/', '', $trimmed) ?? '',
            default => $trimmed,
        };
    }

    public static function validationMessage(string $customerNo, PpobProduct $product): ?string
    {
        $profile = self::profile($product);
        $normalized = self::normalize($customerNo, $product);

        return match ($profile['type']) {
            self::TYPE_MOBILE => self::validateMobile($normalized, $profile['label']),
            self::TYPE_NUMERIC => self::validateNumeric($customerNo, $normalized, $profile['label'], $profile['min'], $profile['max']),
            default => self::validateSafeIdentifier($normalized, $profile['label'], $profile['min'], $profile['max']),
        };
    }

    /**
     * @return array{type: string, label: string, min: int, max: int}
     */
    private static function profile(PpobProduct $product): array
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $product->category,
            $product->brand,
            $product->type,
            $product->product_name,
        ])));
        $isPostpaid = $product->service_type === PpobTransaction::SERVICE_POSTPAID;

        return match (true) {
            $isPostpaid && self::containsAny($haystack, ['telkom', 'indihome', 'internet', 'tv']) => [
                'type' => self::TYPE_NUMERIC,
                'label' => 'ID pelanggan',
                'min' => 6,
                'max' => 20,
            ],
            ! $isPostpaid && self::containsAny($haystack, ['pulsa', 'data', 'internet']) => [
                'type' => self::TYPE_MOBILE,
                'label' => 'Nomor HP tujuan',
                'min' => 10,
                'max' => 13,
            ],
            self::containsAny($haystack, ['ovo', 'gopay', 'dana', 'shopeepay', 'linkaja', 'e-money', 'emoney']) => [
                'type' => self::TYPE_NUMERIC,
                'label' => 'Nomor akun tujuan',
                'min' => 7,
                'max' => 16,
            ],
            self::containsAny($haystack, ['pln', 'listrik']) => [
                'type' => self::TYPE_NUMERIC,
                'label' => 'ID pelanggan atau nomor meter',
                'min' => 8,
                'max' => 16,
            ],
            self::containsAny($haystack, ['bpjs']) => [
                'type' => self::TYPE_NUMERIC,
                'label' => 'Nomor peserta atau VA',
                'min' => 8,
                'max' => 16,
            ],
            self::containsAny($haystack, ['pdam']) => [
                'type' => self::TYPE_NUMERIC,
                'label' => 'Nomor pelanggan',
                'min' => 6,
                'max' => 20,
            ],
            self::containsAny($haystack, ['voucher', 'game', 'garena', 'steam', 'mobile legends', 'free fire']) => [
                'type' => self::TYPE_SAFE_IDENTIFIER,
                'label' => 'User ID atau Zone ID',
                'min' => 4,
                'max' => 40,
            ],
            self::containsAny($haystack, ['finance', 'multifinance', 'angsuran', 'leasing', 'kredit']) => [
                'type' => self::TYPE_SAFE_IDENTIFIER,
                'label' => 'Nomor kontrak',
                'min' => 5,
                'max' => 40,
            ],
            default => [
                'type' => self::TYPE_SAFE_IDENTIFIER,
                'label' => 'Nomor atau ID tujuan',
                'min' => 4,
                'max' => 40,
            ],
        };
    }

    private static function validateMobile(string $normalized, string $label): ?string
    {
        if (! PhoneNumber::isValidIndonesianMobile($normalized)) {
            return $label.' harus nomor seluler Indonesia yang valid.';
        }

        return null;
    }

    private static function validateNumeric(string $raw, string $normalized, string $label, int $min, int $max): ?string
    {
        if (! preg_match('/^[0-9 .-]+$/', trim($raw))) {
            return $label.' hanya boleh berisi angka.';
        }

        $length = strlen($normalized);

        if ($length < $min || $length > $max) {
            return $label.' harus terdiri dari '.$min.' sampai '.$max.' digit.';
        }

        return null;
    }

    private static function validateSafeIdentifier(string $normalized, string $label, int $min, int $max): ?string
    {
        $length = strlen($normalized);

        if ($length < $min || $length > $max) {
            return $label.' harus terdiri dari '.$min.' sampai '.$max.' karakter.';
        }

        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9 ._\\-\\/()]*$/', $normalized)) {
            return $label.' mengandung karakter yang tidak valid.';
        }

        return null;
    }

    private static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
