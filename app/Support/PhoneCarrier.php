<?php

namespace App\Support;

final class PhoneCarrier
{
    /**
     * @var array<string, array<int, string>>
     */
    private const PREFIXES = [
        'BYU' => ['0851'],
        'TELKOMSEL' => ['0811', '0812', '0813', '0821', '0822', '0852', '0853'],
        'INDOSAT' => ['0814', '0815', '0816', '0855', '0856', '0857', '0858'],
        'XL' => ['0817', '0818', '0819', '0859', '0877', '0878'],
        'AXIS' => ['0831', '0832', '0833', '0838'],
        'TRI' => ['0895', '0896', '0897', '0898', '0899'],
        'SMARTFREN' => ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const BRAND_ALIASES = [
        'BYU' => ['BYU'],
        'TELKOMSEL' => ['TELKOMSEL', 'SIMPATI', 'KARTUAS', 'LOOP', 'HALO'],
        'INDOSAT' => ['INDOSAT', 'IM3', 'MENTARI', 'MATRIX', 'INDOSATOOREDOO', 'INDOSATOOREDOOHUTCHISON', 'IOH'],
        'XL' => ['XL', 'XLAXIATA'],
        'AXIS' => ['AXIS'],
        'TRI' => ['TRI', 'THREE', '3'],
        'SMARTFREN' => ['SMARTFREN', 'SMART'],
    ];

    public static function detectBrandKey(string $phoneNumber): ?string
    {
        $normalized = PhoneNumber::normalize($phoneNumber);

        if (strlen($normalized) < 4) {
            return null;
        }

        foreach (self::PREFIXES as $brandKey => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($normalized, $prefix)) {
                    return $brandKey;
                }
            }
        }

        return null;
    }

    public static function matchBrandKey(string $brandLabel): ?string
    {
        $normalizedLabel = self::normalizeBrandLabel($brandLabel);

        foreach (self::BRAND_ALIASES as $brandKey => $aliases) {
            if (in_array($normalizedLabel, $aliases, true)) {
                return $brandKey;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function browserConfig(): array
    {
        return [
            'prefixes' => self::PREFIXES,
        ];
    }

    private static function normalizeBrandLabel(string $brandLabel): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]+/', '', $brandLabel) ?? '');
    }
}
