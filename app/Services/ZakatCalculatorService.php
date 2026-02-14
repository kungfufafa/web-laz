<?php

namespace App\Services;

class ZakatCalculatorService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function calculate(string $type, array $data): ?array
    {
        return match ($type) {
            'fitrah' => $this->calculateFitrah($data),
            'maal' => $this->calculateMaal($data),
            'profesi' => $this->calculateProfesi($data),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function calculateFitrah(array $data): ?array
    {
        $peopleCount = (int) ($data['people_count'] ?? 0);
        $ricePricePerKg = (float) ($data['rice_price_per_kg'] ?? 0);
        $riceKgPerPerson = (float) config('donation.zakat_defaults.fitrah_rice_kg_per_person');

        if ($peopleCount < 1 || $ricePricePerKg <= 0) {
            return null;
        }

        $recommendedAmount = round($peopleCount * $ricePricePerKg * $riceKgPerPerson, 2);

        return [
            'recommended_amount' => $recommendedAmount,
            'is_obligatory' => true,
            'summary' => 'Zakat fitrah dihitung berdasarkan jumlah jiwa dan harga beras per kilogram.',
            'breakdown' => [
                'people_count' => $peopleCount,
                'rice_price_per_kg' => $ricePricePerKg,
                'rice_kg_per_person' => $riceKgPerPerson,
                'formula' => sprintf('%d x %.2f x %.1f', $peopleCount, $ricePricePerKg, $riceKgPerPerson),
                'result' => $recommendedAmount,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function calculateMaal(array $data): ?array
    {
        $totalAssets = (float) ($data['total_assets'] ?? 0);
        $shortTermDebt = (float) ($data['short_term_debt'] ?? 0);
        $goldPricePerGram = (float) ($data['gold_price_per_gram'] ?? 0);
        $goldGramsNisab = (float) ($data['gold_grams_nisab'] ?? config('donation.zakat_defaults.maal_nisab_gold_grams'));
        $haulPassed = (bool) ($data['haul_passed'] ?? false);

        if ($totalAssets <= 0 || $goldPricePerGram <= 0 || $goldGramsNisab <= 0) {
            return null;
        }

        $netAssets = max($totalAssets - $shortTermDebt, 0);
        $nisabAmount = $goldPricePerGram * $goldGramsNisab;
        $isObligatory = $haulPassed && $netAssets >= $nisabAmount;
        $recommendedAmount = $isObligatory ? round($netAssets * 0.025, 2) : 0.0;

        return [
            'recommended_amount' => $recommendedAmount,
            'is_obligatory' => $isObligatory,
            'summary' => $isObligatory
                ? 'Harta sudah memenuhi nisab dan haul, zakat maal wajib ditunaikan.'
                : 'Belum memenuhi nisab/haul. Jika ingin tetap berdonasi dapat diarahkan ke sedekah.',
            'breakdown' => [
                'total_assets' => $totalAssets,
                'short_term_debt' => $shortTermDebt,
                'net_assets' => $netAssets,
                'gold_price_per_gram' => $goldPricePerGram,
                'gold_grams_nisab' => $goldGramsNisab,
                'nisab_amount' => $nisabAmount,
                'haul_passed' => $haulPassed,
                'rate' => 0.025,
                'result' => $recommendedAmount,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function calculateProfesi(array $data): ?array
    {
        $monthlyIncome = (float) ($data['monthly_income'] ?? 0);
        $monthlyNeeds = (float) ($data['monthly_needs'] ?? 0);
        $periodMonths = (int) ($data['period_months'] ?? 1);
        $goldPricePerGram = (float) ($data['gold_price_per_gram'] ?? 0);
        $goldGramsNisab = (float) ($data['gold_grams_nisab'] ?? config('donation.zakat_defaults.profesi_nisab_gold_grams'));

        if ($monthlyIncome <= 0 || $goldPricePerGram <= 0 || $goldGramsNisab <= 0) {
            return null;
        }

        $netMonthlyIncome = max($monthlyIncome - $monthlyNeeds, 0);
        $netIncomeForPeriod = $netMonthlyIncome * max($periodMonths, 1);
        $annualNetIncome = $netMonthlyIncome * 12;
        $nisabAmount = $goldPricePerGram * $goldGramsNisab;
        $isObligatory = $annualNetIncome >= $nisabAmount;
        $recommendedAmount = $isObligatory ? round($netIncomeForPeriod * 0.025, 2) : 0.0;

        return [
            'recommended_amount' => $recommendedAmount,
            'is_obligatory' => $isObligatory,
            'summary' => $isObligatory
                ? 'Penghasilan bersih tahunan telah mencapai nisab.'
                : 'Penghasilan bersih tahunan belum mencapai nisab.',
            'breakdown' => [
                'monthly_income' => $monthlyIncome,
                'monthly_needs' => $monthlyNeeds,
                'net_monthly_income' => $netMonthlyIncome,
                'period_months' => max($periodMonths, 1),
                'net_income_for_period' => $netIncomeForPeriod,
                'annual_net_income' => $annualNetIncome,
                'gold_price_per_gram' => $goldPricePerGram,
                'gold_grams_nisab' => $goldGramsNisab,
                'nisab_amount' => $nisabAmount,
                'rate' => 0.025,
                'result' => $recommendedAmount,
            ],
        ];
    }
}
