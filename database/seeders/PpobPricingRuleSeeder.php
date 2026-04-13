<?php

namespace Database\Seeders;

use App\Models\PpobPricingRule;
use Illuminate\Database\Seeder;

class PpobPricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        PpobPricingRule::query()->updateOrCreate(
            ['name' => 'Default PPOB Prepaid'],
            [
                'service_type' => 'prepaid',
                'category' => null,
                'brand' => null,
                'buyer_sku_code' => null,
                'markup_type' => PpobPricingRule::MARKUP_FIXED,
                'markup_value' => 1500,
                'min_markup' => null,
                'max_markup' => null,
                'rounding_unit' => 100,
                'priority' => 100,
                'is_active' => true,
                'notes' => 'Baseline margin default untuk produk PPOB prabayar.',
            ],
        );

        PpobPricingRule::query()->updateOrCreate(
            ['name' => 'Default PPOB Postpaid'],
            [
                'service_type' => 'postpaid',
                'category' => null,
                'brand' => null,
                'buyer_sku_code' => null,
                'markup_type' => PpobPricingRule::MARKUP_PERCENT,
                'markup_value' => 2.5,
                'min_markup' => 2500,
                'max_markup' => 6000,
                'rounding_unit' => 500,
                'priority' => 100,
                'is_active' => true,
                'notes' => 'Baseline margin default untuk produk PPOB pascabayar.',
            ],
        );
    }
}
