<?php

use App\Filament\Resources\PpobPricingRules\Pages\CreatePpobPricingRule;
use App\Filament\Resources\PpobProducts\Pages\ListPpobProducts;
use App\Filament\Resources\PpobTransactions\Pages\ListPpobTransactions;
use App\Models\PpobPricingRule;
use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');
    config()->set('services.digiflazz.username', 'digiflazz-user');
    config()->set('services.digiflazz.api_key', 'digiflazz-key');
});

test('admin can sync prepaid ppob products from filament list page', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/price-list' => Http::response([
            'data' => [
                [
                    'buyer_sku_code' => 'XL10',
                    'product_name' => 'XL 10.000',
                    'category' => 'Pulsa',
                    'brand' => 'XL',
                    'type' => 'Umum',
                    'price' => 10000,
                    'buyer_product_status' => true,
                    'seller_product_status' => true,
                ],
            ],
        ]),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListPpobProducts::class)
        ->callAction('syncPrepaid');

    expect(PpobProduct::query()->where('buyer_sku_code', 'XL10')->exists())->toBeTrue();
});

test('admin prepaid sync deactivates digiflazz skus missing from the latest catalog', function (): void {
    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 5.000',
        'buyer_sku_code' => 'XL5',
        'price' => 5000,
        'provider_price' => 5000,
        'sell_price' => 6500,
        'markup_amount' => 1500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    Http::fake([
        'https://api.digiflazz.com/v1/price-list' => Http::response([
            'data' => [
                [
                    'buyer_sku_code' => 'XL10',
                    'product_name' => 'XL 10.000',
                    'category' => 'Pulsa',
                    'brand' => 'XL',
                    'type' => 'Umum',
                    'price' => 10000,
                    'buyer_product_status' => true,
                    'seller_product_status' => true,
                ],
            ],
        ]),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListPpobProducts::class)
        ->callAction('syncPrepaid');

    $missingProduct = PpobProduct::query()->where('buyer_sku_code', 'XL5')->first();

    expect($missingProduct)->not()->toBeNull();
    expect($missingProduct?->buyer_product_status)->toBeFalse();
    expect($missingProduct?->seller_product_status)->toBeFalse();
    expect(PpobProduct::query()->where('buyer_sku_code', 'XL10')->exists())->toBeTrue();
});

test('admin prepaid sync keeps existing catalog active when digiflazz returns an empty list', function (): void {
    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 5.000',
        'buyer_sku_code' => 'XL5',
        'price' => 5000,
        'provider_price' => 5000,
        'sell_price' => 6500,
        'markup_amount' => 1500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    Http::fake([
        'https://api.digiflazz.com/v1/price-list' => Http::response([
            'data' => [],
        ]),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListPpobProducts::class)
        ->callAction('syncPrepaid');

    $existingProduct = PpobProduct::query()->where('buyer_sku_code', 'XL5')->first();

    expect($existingProduct)->not()->toBeNull();
    expect($existingProduct?->buyer_product_status)->toBeTrue();
    expect($existingProduct?->seller_product_status)->toBeTrue();
});

test('admin can retry fulfillment from ppob transactions list', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::response([
            'data' => [
                'ref_id' => 'PPOBREF001',
                'customer_no' => '081234567890',
                'buyer_sku_code' => 'XL10',
                'message' => 'Transaksi Sukses',
                'status' => 'Sukses',
                'rc' => '00',
                'sn' => 'SN-123',
                'price' => 10000,
            ],
        ]),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $transaction = PpobTransaction::query()->create([
        'user_id' => $admin->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 10000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_order_id' => 'INV-001',
        'digiflazz_ref_id' => 'PPOBREF001',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListPpobTransactions::class)
        ->assertTableActionVisible('retryFulfillment', $transaction)
        ->callTableAction('retryFulfillment', $transaction)
        ->assertHasNoTableActionErrors();

    expect($transaction->refresh()->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_SUCCEEDED);
});

test('retry fulfillment action is hidden for transactions that are already processing', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $transaction = PpobTransaction::query()->create([
        'user_id' => $admin->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 10000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PROCESSING,
        'payment_gateway_order_id' => 'INV-001-PROCESSING',
        'digiflazz_ref_id' => 'PPOBREF-001-PROCESSING',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListPpobTransactions::class)
        ->assertTableActionHidden('retryFulfillment', $transaction);
});

test('member with view-only ppob transaction permission cannot see refresh status action', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $transaction = PpobTransaction::query()->create([
        'user_id' => $member->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 10000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_order_id' => 'INV-001-VIEW',
        'digiflazz_ref_id' => 'PPOBREF-001-VIEW',
    ]);

    $member->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'ViewAny:PpobTransaction',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'View:PpobTransaction',
            'guard_name' => 'web',
        ]),
    ]);

    $this->actingAs($member);

    Livewire::test(ListPpobTransactions::class)
        ->assertTableActionHidden('refreshStatus', $transaction);
});

test('member with update ppob transaction permission can see refresh status action', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $transaction = PpobTransaction::query()->create([
        'user_id' => $member->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 10000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_order_id' => 'INV-001-UPDATE',
        'digiflazz_ref_id' => 'PPOBREF-001-UPDATE',
    ]);

    $member->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'ViewAny:PpobTransaction',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'View:PpobTransaction',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'Update:PpobTransaction',
            'guard_name' => 'web',
        ]),
    ]);

    $this->actingAs($member);

    Livewire::test(ListPpobTransactions::class)
        ->assertTableActionVisible('refreshStatus', $transaction);
});

test('member with view-only ppob product permission cannot see catalog sync actions', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $member->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'ViewAny:PpobProduct',
            'guard_name' => 'web',
        ]),
    ]);

    $this->actingAs($member);

    Livewire::test(ListPpobProducts::class)
        ->assertActionHidden('syncPrepaid')
        ->assertActionHidden('syncPostpaid');
});

test('member with update ppob product permission can see catalog sync actions', function (): void {
    $member = User::factory()->create([
        'role' => 'member',
    ]);

    $member->givePermissionTo([
        Permission::query()->firstOrCreate([
            'name' => 'ViewAny:PpobProduct',
            'guard_name' => 'web',
        ]),
        Permission::query()->firstOrCreate([
            'name' => 'Update:PpobProduct',
            'guard_name' => 'web',
        ]),
    ]);

    $this->actingAs($member);

    Livewire::test(ListPpobProducts::class)
        ->assertActionVisible('syncPrepaid')
        ->assertActionVisible('syncPostpaid');
});

test('admin can create ppob pricing rule from filament form', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreatePpobPricingRule::class)
        ->fillForm([
            'name' => 'Margin XL Prepaid',
            'service_type' => 'prepaid',
            'brand' => 'XL',
            'markup_type' => 'fixed',
            'markup_value' => 2500,
            'rounding_unit' => 100,
            'priority' => 10,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PpobPricingRule::query()->where('name', 'Margin XL Prepaid')->exists())->toBeTrue();
});
