<?php

namespace Database\Seeders;

use App\Models\DonationCategory;
use App\Models\DonationPaymentType;
use App\Services\ZakatCalculatorService;
use Illuminate\Database\Seeder;

class DonationConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = config('donation.categories', []);
        $contexts = config('donation.contexts', []);
        $zakatCalculatorTypes = config('donation.zakat_calculator_types', ZakatCalculatorService::supportedTypes());

        if (! is_array($categories)) {
            return;
        }

        $categorySortOrder = 0;
        foreach ($categories as $categoryKey => $categoryConfig) {
            if (! is_string($categoryKey) || ! is_array($categoryConfig)) {
                continue;
            }

            $normalizedCategoryKey = strtolower(trim($categoryKey));
            if ($normalizedCategoryKey === '') {
                continue;
            }

            $category = DonationCategory::withTrashed()
                ->firstOrNew(['key' => $normalizedCategoryKey]);

            $category->label = is_string($categoryConfig['label'] ?? null) && trim($categoryConfig['label']) !== ''
                ? trim($categoryConfig['label'])
                : ucfirst($normalizedCategoryKey);
            $category->description = is_string($categoryConfig['description'] ?? null)
                ? trim($categoryConfig['description'])
                : null;
            $category->requires_context = (bool) ($categoryConfig['requires_context']
                ?? ! empty($contexts[$normalizedCategoryKey] ?? []));
            $category->sort_order = (int) ($categoryConfig['sort_order'] ?? $categorySortOrder);
            $category->is_active = (bool) ($categoryConfig['is_active'] ?? true);
            $category->is_locked = (bool) ($categoryConfig['is_locked'] ?? true);
            $category->deleted_at = null;
            $category->save();

            $paymentTypes = $categoryConfig['payment_types'] ?? [];
            if (! is_array($paymentTypes)) {
                $paymentTypes = [];
            }

            $paymentTypeSortOrder = 0;
            foreach ($paymentTypes as $paymentTypeKey => $paymentTypeConfig) {
                if (! is_string($paymentTypeKey)) {
                    continue;
                }

                $normalizedPaymentTypeKey = strtolower(trim($paymentTypeKey));
                if ($normalizedPaymentTypeKey === '') {
                    continue;
                }

                $paymentType = DonationPaymentType::withTrashed()
                    ->firstOrNew([
                        'donation_category_id' => $category->id,
                        'key' => $normalizedPaymentTypeKey,
                    ]);

                $paymentTypeLabel = is_array($paymentTypeConfig)
                    ? $paymentTypeConfig['label'] ?? null
                    : $paymentTypeConfig;

                $paymentType->label = is_string($paymentTypeLabel) && trim($paymentTypeLabel) !== ''
                    ? trim($paymentTypeLabel)
                    : ucfirst($normalizedPaymentTypeKey);
                $paymentType->description = is_array($paymentTypeConfig) && is_string($paymentTypeConfig['description'] ?? null)
                    ? trim((string) $paymentTypeConfig['description'])
                    : null;
                if ($paymentType->description === '') {
                    $paymentType->description = null;
                }

                $isZakatCalculator = is_array($paymentTypeConfig)
                    ? ($paymentTypeConfig['is_zakat_calculator'] ?? null)
                    : null;

                $paymentType->is_zakat_calculator = is_bool($isZakatCalculator)
                    ? $isZakatCalculator
                    : in_array($normalizedPaymentTypeKey, $zakatCalculatorTypes, true);
                $paymentType->conditions = is_array($paymentTypeConfig) && is_array($paymentTypeConfig['conditions'] ?? null)
                    ? $paymentTypeConfig['conditions']
                    : null;
                $paymentType->sort_order = is_array($paymentTypeConfig) && isset($paymentTypeConfig['sort_order'])
                    ? (int) $paymentTypeConfig['sort_order']
                    : $paymentTypeSortOrder;
                $paymentType->is_active = is_array($paymentTypeConfig)
                    ? (bool) ($paymentTypeConfig['is_active'] ?? true)
                    : true;
                $paymentType->is_locked = is_array($paymentTypeConfig)
                    ? (bool) ($paymentTypeConfig['is_locked'] ?? true)
                    : true;
                $paymentType->deleted_at = null;
                $paymentType->save();
                $paymentTypeSortOrder++;
            }

            $categorySortOrder++;
        }
    }
}
