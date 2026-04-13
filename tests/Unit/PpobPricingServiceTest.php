<?php

use App\Models\PpobPricingRule;
use App\Services\PpobPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('more specific ppob pricing rule beats global rule', function (): void {
    PpobPricingRule::query()->create([
        'name' => 'Global Rule',
        'markup_type' => PpobPricingRule::MARKUP_FIXED,
        'markup_value' => 1000,
        'rounding_unit' => 1,
        'priority' => 100,
        'is_active' => true,
    ]);

    $brandRule = PpobPricingRule::query()->create([
        'name' => 'XL Brand Rule',
        'brand' => 'XL',
        'markup_type' => PpobPricingRule::MARKUP_FIXED,
        'markup_value' => 2500,
        'rounding_unit' => 100,
        'priority' => 10,
        'is_active' => true,
    ]);

    /** @var PpobPricingService $service */
    $service = app(PpobPricingService::class);
    $pricing = $service->resolvePricing([
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'buyer_sku_code' => 'XL10',
    ], 10100);

    expect($pricing['rule'])->toBeInstanceOf(PpobPricingRule::class)
        ->and($pricing['rule']?->id)->toBe($brandRule->id)
        ->and($pricing['provider_price'])->toBe(10100.0)
        ->and($pricing['markup_amount'])->toBe(2500.0)
        ->and($pricing['sell_price'])->toBe(12600.0);
});

test('percent ppob pricing rule respects min max and rounding', function (): void {
    PpobPricingRule::query()->create([
        'name' => 'Postpaid Percent Rule',
        'service_type' => 'postpaid',
        'markup_type' => PpobPricingRule::MARKUP_PERCENT,
        'markup_value' => 2.5,
        'min_markup' => 3000,
        'max_markup' => 6000,
        'rounding_unit' => 500,
        'priority' => 5,
        'is_active' => true,
    ]);

    /** @var PpobPricingService $service */
    $service = app(PpobPricingService::class);
    $pricing = $service->resolvePricing([
        'service_type' => 'postpaid',
        'category' => 'Listrik',
        'brand' => 'PLN',
        'buyer_sku_code' => 'PLN',
    ], 187500);

    expect($pricing['provider_price'])->toBe(187500.0)
        ->and($pricing['markup_amount'])->toBe(5000.0)
        ->and($pricing['sell_price'])->toBe(192500.0);
});
