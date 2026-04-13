<?php

use App\Models\PpobPricingRule;
use Database\Seeders\PpobPricingRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ppob pricing rule seeder creates default prepaid and postpaid rules', function (): void {
    $this->seed(PpobPricingRuleSeeder::class);

    $prepaid = PpobPricingRule::query()->where('name', 'Default PPOB Prepaid')->first();
    $postpaid = PpobPricingRule::query()->where('name', 'Default PPOB Postpaid')->first();

    expect($prepaid)->not()->toBeNull()
        ->and($prepaid?->service_type)->toBe('prepaid')
        ->and((float) $prepaid?->markup_value)->toBe(1500.0);

    expect($postpaid)->not()->toBeNull()
        ->and($postpaid?->service_type)->toBe('postpaid')
        ->and($postpaid?->markup_type)->toBe(PpobPricingRule::MARKUP_PERCENT)
        ->and((float) $postpaid?->min_markup)->toBe(2500.0)
        ->and((float) $postpaid?->max_markup)->toBe(6000.0);
});
