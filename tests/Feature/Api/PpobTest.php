<?php

use App\Jobs\ProcessPpobFulfillment;
use App\Jobs\ReconcilePpobTransaction;
use App\Models\PpobPricingRule;
use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use App\Services\PpobTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.midtrans.server_key', 'midtrans-server-key');
    config()->set('services.midtrans.client_key', 'midtrans-client-key');
    config()->set('services.midtrans.merchant_id', 'G123456789');
    config()->set('services.midtrans.is_production', false);
    config()->set('services.midtrans.enabled_payments', ['bri_va', 'qris', 'gopay']);
    config()->set('services.tripay.base_url', 'https://tripay.co.id/api');
    config()->set('services.tripay.api_key', 'tripay-api-key');
    config()->set('services.tripay.private_key', 'tripay-private-key');
    config()->set('services.tripay.merchant_code', 'T0001');
    config()->set('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');
    config()->set('services.digiflazz.username', 'digiflazz-user');
    config()->set('services.digiflazz.api_key', 'digiflazz-key');
    config()->set('services.digiflazz.webhook_secret', 'digiflazz-secret');
    config()->set('services.ppob.payment_gateway', 'midtrans');
    config()->set('services.ppob.fulfillment_dispatch', 'sync');
});

test('can list configured midtrans payment channels', function () {
    $response = $this->getJson('/api/ppob/payment-channels');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.code', 'BRIVA')
        ->assertJsonPath('data.0.midtrans_code', 'bri_va')
        ->assertJsonPath('data.1.code', 'QRIS')
        ->assertJsonPath('data.2.code', 'GOPAY');
});

test('can list active ppob products', function () {
    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 10.000',
        'buyer_sku_code' => 'XL10',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 20.000',
        'buyer_sku_code' => 'XL20',
        'price' => 20000,
        'buyer_product_status' => false,
        'seller_product_status' => true,
    ]);

    $response = $this->getJson('/api/ppob/products?service_type=prepaid');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'buyer_sku_code' => 'XL10',
            'service_type' => 'prepaid',
            'price' => 12500,
            'provider_price' => 10000,
            'sell_price' => 12500,
            'markup_amount' => 2500,
        ])
        ->assertJsonMissing([
            'buyer_sku_code' => 'XL20',
        ]);
});

test('authenticated user can create prepaid ppob transaction with midtrans snap checkout data', function () {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-001',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-001',
        ]),
    ]);

    $user = User::factory()->create();
    $pricingRule = PpobPricingRule::query()->create([
        'name' => 'Markup XL',
        'brand' => 'XL',
        'markup_type' => 'fixed',
        'markup_value' => 2500,
        'rounding_unit' => 100,
        'priority' => 10,
        'is_active' => true,
    ]);

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 10.000',
        'buyer_sku_code' => 'XL10',
        'price' => 10000,
        'ppob_pricing_rule_id' => $pricingRule->id,
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/transactions', [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.service_type', 'prepaid')
        ->assertJsonPath('data.payment_status', 'unpaid')
        ->assertJsonPath('data.payment_gateway', 'midtrans')
        ->assertJsonPath('data.payment_channel_code', 'BRIVA')
        ->assertJsonPath('data.provider_price', 10000)
        ->assertJsonPath('data.markup_amount', 2500)
        ->assertJsonPath('data.base_price', 12500)
        ->assertJsonPath('data.total_amount', 12500)
        ->assertJsonPath('data.checkout_url', 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-001')
        ->assertJsonPath('data.midtrans_snap_token', 'snap-token-001')
        ->assertJsonPath('data.pay_code', null);

    expect($response->json('data.tripay_reference'))->toBeNull();
    expect($response->json('data.tripay_merchant_ref'))->toBe($response->json('data.payment_order_id'));

    $transaction = PpobTransaction::query()->first();
    expect($transaction)->not()->toBeNull();
    expect($transaction?->buyer_sku_code)->toBe('XL10');
    expect($transaction?->payment_status)->toBe(PpobTransaction::PAYMENT_UNPAID);
    expect($transaction?->midtrans_order_id)->not->toBeNull();
    expect($transaction?->midtrans_snap_token)->toBe('snap-token-001');
    expect($transaction?->midtrans_expired_at?->between(now()->addMinutes(59), now()->addMinutes(61)))->toBeTrue();
    expect((float) $transaction?->provider_price)->toEqual(10000.0);
    expect((float) $transaction?->markup_amount)->toEqual(2500.0);
    expect((float) $transaction?->base_price)->toEqual(12500.0);

    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();

        return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && data_get($payload, 'expiry.duration') === 60
            && data_get($payload, 'expiry.unit') === 'minute'
            && is_string(data_get($payload, 'expiry.start_time'))
            && str_ends_with((string) data_get($payload, 'expiry.start_time'), '+0700')
            && data_get($payload, 'page_expiry.duration') === 60
            && data_get($payload, 'page_expiry.unit') === 'minute';
    });
});

test('authenticated user can create prepaid ppob transaction using legacy tripay_method alias', function () {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-legacy',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-legacy',
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 10.000',
        'buyer_sku_code' => 'XL10',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/transactions', [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'tripay_method' => 'BRIVA',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.payment_channel_code', 'BRIVA')
        ->assertJsonPath('data.payment_gateway', 'midtrans')
        ->assertJsonPath('data.midtrans_snap_token', 'snap-token-legacy');
});

test('api rejects malformed prepaid customer numbers across different product types', function () {
    Http::fake();

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Voucher',
        'brand' => 'Mobile Legends',
        'type' => 'Game',
        'product_name' => 'ML 86 Diamonds',
        'buyer_sku_code' => 'ML86',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 21500,
        'markup_amount' => 1500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/transactions', [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'ML86',
        'customer_no' => 'ml<script>alert(1)</script>',
        'payment_channel_code' => 'BRIVA',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_no']);

    expect(PpobTransaction::query()->count())->toBe(0);

    Http::assertNothingSent();
});

test('api rejects malformed postpaid inquiry identifiers before hitting provider', function () {
    Http::fake();

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'PLN',
        'brand' => 'PLN',
        'type' => 'Tagihan',
        'product_name' => 'PLN Pascabayar',
        'buyer_sku_code' => 'PLNPOST',
        'price' => 50000,
        'provider_price' => 50000,
        'sell_price' => 52000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/inquiries', [
        'buyer_sku_code' => 'PLNPOST',
        'customer_no' => 'PLN<script>',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_no']);

    Http::assertNothingSent();
});

test('api accepts numeric customer numbers for postpaid internet bill inquiries', function () {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::response([
            'data' => [
                'ref_id' => 'PPOBINTERNET001',
                'customer_no' => '1234567890',
                'customer_name' => 'Pelanggan IndiHome',
                'buyer_sku_code' => 'INDIHOMEPOST',
                'message' => 'Inquiry berhasil',
                'status' => 'Sukses',
                'price' => 350000,
                'selling_price' => 350000,
            ],
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'Internet',
        'brand' => 'IndiHome',
        'type' => 'Tagihan',
        'product_name' => 'Tagihan Internet IndiHome',
        'buyer_sku_code' => 'INDIHOMEPOST',
        'price' => 350000,
        'provider_price' => 350000,
        'sell_price' => 352500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/inquiries', [
        'buyer_sku_code' => 'INDIHOMEPOST',
        'customer_no' => '1234567890',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.customer_no', '1234567890')
        ->assertJsonPath('data.customer_name', 'Pelanggan IndiHome')
        ->assertJsonPath('data.buyer_sku_code', 'INDIHOMEPOST');
});

test('can list active tripay payment channels when tripay is configured', function () {
    config()->set('services.ppob.payment_gateway', 'tripay');

    Http::fake([
        'https://tripay.co.id/api/merchant/payment-channel' => Http::response([
            'data' => [
                [
                    'code' => 'BRIVA',
                    'name' => 'BRI Virtual Account',
                    'active' => true,
                ],
                [
                    'code' => 'QRIS',
                    'name' => 'QRIS',
                    'active' => false,
                ],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/ppob/payment-channels');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'BRIVA')
        ->assertJsonPath('data.0.name', 'BRI Virtual Account');
});

test('authenticated user can create prepaid ppob transaction with tripay checkout data', function () {
    config()->set('services.ppob.payment_gateway', 'tripay');
    config()->set('services.tripay.return_url', 'https://example.test/tripay/return');

    Http::fake([
        'https://tripay.co.id/api/merchant/payment-channel' => Http::response([
            'data' => [
                [
                    'code' => 'BRIVA',
                    'name' => 'BRI Virtual Account',
                    'active' => true,
                ],
            ],
        ]),
        'https://tripay.co.id/api/payment/instruction*' => Http::response([
            'data' => [[
                'title' => 'Bayar via BRIVA',
                'steps' => ['Lakukan pembayaran sebelum kedaluwarsa.'],
            ]],
        ]),
        'https://tripay.co.id/api/transaction/create' => Http::response([
            'data' => [
                'reference' => 'TRIPAY-REF-001',
                'payment_method' => 'BRIVA',
                'payment_name' => 'BRI Virtual Account',
                'checkout_url' => 'https://tripay.test/checkout/TRIPAY-REF-001',
                'pay_url' => 'https://tripay.test/pay/TRIPAY-REF-001',
                'pay_code' => '1234567890',
                'expired_time' => now()->addHour()->timestamp,
                'amount' => 12500,
                'amount_received' => 0,
                'fee_customer' => 0,
                'fee_merchant' => 0,
                'status' => 'UNPAID',
            ],
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'XL',
        'type' => 'Umum',
        'product_name' => 'XL 10.000',
        'buyer_sku_code' => 'XL10',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/ppob/transactions', [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.payment_gateway', 'tripay')
        ->assertJsonPath('data.payment_channel_code', 'BRIVA')
        ->assertJsonPath('data.checkout_url', 'https://tripay.test/checkout/TRIPAY-REF-001')
        ->assertJsonPath('data.pay_url', 'https://tripay.test/pay/TRIPAY-REF-001')
        ->assertJsonPath('data.pay_code', '1234567890')
        ->assertJsonPath('data.payment_reference', 'TRIPAY-REF-001')
        ->assertJsonPath('data.tripay_reference', 'TRIPAY-REF-001');

    $transaction = PpobTransaction::query()->first();
    expect($transaction)->not()->toBeNull();
    expect($transaction?->resolvedPaymentGateway())->toBe('tripay');
    expect($transaction?->payment_gateway_reference)->toBe('TRIPAY-REF-001');
    expect($transaction?->payment_gateway_order_id)->not->toBeNull();
});

test('midtrans paid callback dispatches fulfillment when queue mode is enabled', function () {
    Bus::fake();
    config()->set('services.ppob.fulfillment_dispatch', 'queue');
    $settlementTime = now()->format('Y-m-d H:i:s');

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
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
        'midtrans_order_id' => 'PPOB-ORDER-001',
        'payment_gateway_order_id' => 'PPOB-ORDER-001',
        'digiflazz_ref_id' => 'PPOBREF001',
    ]);

    Http::fake([
        'https://api.sandbox.midtrans.com/v2/PPOB-ORDER-001/status' => Http::response([
            'transaction_id' => 'midtrans-transaction-001',
            'order_id' => 'PPOB-ORDER-001',
            'gross_amount' => '10000.00',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
            'transaction_time' => $settlementTime,
            'settlement_time' => $settlementTime,
            'va_numbers' => [
                ['bank' => 'bri', 'va_number' => '1234567890'],
            ],
        ]),
    ]);

    $payload = [
        'transaction_id' => 'midtrans-transaction-001',
        'order_id' => 'PPOB-ORDER-001',
        'gross_amount' => '10000.00',
        'status_code' => '200',
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
        'fraud_status' => 'accept',
        'transaction_time' => $settlementTime,
        'settlement_time' => $settlementTime,
        'va_numbers' => [
            ['bank' => 'bri', 'va_number' => '1234567890'],
        ],
    ];
    $payload['signature_key'] = hash('sha512', 'PPOB-ORDER-001'.'200'.'10000.00'.'midtrans-server-key');

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/midtrans',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
        ],
        json_encode($payload, JSON_THROW_ON_ERROR),
    );

    $response->assertOk()
        ->assertJson(['success' => true]);

    Bus::assertDispatched(ProcessPpobFulfillment::class, function (ProcessPpobFulfillment $job) use ($transaction): bool {
        return $job->transactionUuid === $transaction->uuid;
    });
});

test('midtrans callback is rejected when server key is not configured', function () {
    config()->set('services.midtrans.server_key', '');

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 0,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'midtrans_order_id' => 'PPOB-ORDER-NO-KEY',
        'payment_gateway_order_id' => 'PPOB-ORDER-NO-KEY',
        'digiflazz_ref_id' => 'PPOBREF-NO-KEY',
    ]);

    $payload = [
        'transaction_id' => 'midtrans-transaction-no-key',
        'order_id' => 'PPOB-ORDER-NO-KEY',
        'gross_amount' => '10000.00',
        'status_code' => '200',
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
        'fraud_status' => 'accept',
    ];
    $payload['signature_key'] = hash('sha512', 'PPOB-ORDER-NO-KEY'.'200'.'10000.00');

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/midtrans',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
        ],
        json_encode($payload, JSON_THROW_ON_ERROR),
    );

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid signature.',
        ]);

    expect($transaction->fresh()->payment_status)->toBe(PpobTransaction::PAYMENT_UNPAID);
    expect($transaction->fresh()->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_PENDING);
});

test('tripay paid callback dispatches fulfillment when queue mode is enabled', function () {
    Bus::fake();
    config()->set('services.ppob.payment_gateway', 'tripay');
    config()->set('services.ppob.fulfillment_dispatch', 'queue');

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 0,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_reference' => 'TRIPAY-REF-PAID',
        'payment_gateway_order_id' => 'PPOB-TRIPAY-PAID',
        'digiflazz_ref_id' => 'PPOBREF-TRIPAY-PAID',
        'metadata' => [
            'payment_gateway' => 'tripay',
        ],
    ]);

    $payload = [
        'reference' => 'TRIPAY-REF-PAID',
        'merchant_ref' => 'PPOB-TRIPAY-PAID',
        'status' => 'PAID',
        'payment_method' => 'BRI Virtual Account',
        'payment_method_code' => 'BRIVA',
        'amount' => 12500,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'paid_at' => now()->timestamp,
    ];
    $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/tripay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CALLBACK_SIGNATURE' => hash_hmac('sha256', $rawBody, 'tripay-private-key'),
            'HTTP_X_CALLBACK_EVENT' => 'payment_status',
        ],
        $rawBody,
    );

    $response->assertOk()
        ->assertJson(['success' => true]);

    expect($transaction->fresh()->payment_status)->toBe(PpobTransaction::PAYMENT_PAID);

    Bus::assertDispatched(ProcessPpobFulfillment::class, function (ProcessPpobFulfillment $job) use ($transaction): bool {
        return $job->transactionUuid === $transaction->uuid;
    });
});

test('tripay callback is rejected when private key is not configured', function () {
    config()->set('services.tripay.private_key', '');

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 0,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_reference' => 'TRIPAY-REF-NO-KEY',
        'payment_gateway_order_id' => 'PPOB-TRIPAY-NO-KEY',
        'digiflazz_ref_id' => 'PPOBREF-TRIPAY-NO-KEY',
        'metadata' => [
            'payment_gateway' => 'tripay',
        ],
    ]);

    $payload = [
        'reference' => 'TRIPAY-REF-NO-KEY',
        'merchant_ref' => 'PPOB-TRIPAY-NO-KEY',
        'status' => 'PAID',
        'payment_method' => 'BRI Virtual Account',
        'payment_method_code' => 'BRIVA',
        'amount' => 12500,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'paid_at' => now()->timestamp,
    ];
    $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/tripay',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CALLBACK_SIGNATURE' => hash_hmac('sha256', $rawBody, ''),
            'HTTP_X_CALLBACK_EVENT' => 'payment_status',
        ],
        $rawBody,
    );

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid signature.',
        ]);

    expect($transaction->fresh()->payment_status)->toBe(PpobTransaction::PAYMENT_UNPAID);
    expect($transaction->fresh()->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_PENDING);
});

test('processing fulfillment updates prepaid transaction from digiflazz response', function () {
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

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
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

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->fulfillTransaction($transaction->uuid);

    $transaction->refresh();

    expect($transaction->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_SUCCEEDED);
    expect($transaction->digiflazz_status)->toBe('Sukses');
    expect($transaction->digiflazz_sn)->toBe('SN-123');
});

test('processing fulfillment does not re-submit transaction that is already processing', function () {
    Http::fake();

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
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
        'digiflazz_ref_id' => 'PPOBREF-PROCESSING',
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $result = $service->fulfillTransaction($transaction->uuid);

    expect($result->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_PROCESSING);
    Http::assertNothingSent();
});

test('processing paid postpaid transaction with expired inquiry sends it to manual review', function () {
    Http::fake();

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'buyer_sku_code' => 'PLNPOST',
        'product_name' => 'PLN Pascabayar',
        'customer_no' => '123456789012',
        'customer_name' => 'Pelanggan PLN',
        'provider_price' => 50000,
        'markup_amount' => 2000,
        'base_price' => 52000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 52000,
        'amount_received' => 52000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'paid_at' => now()->subMinutes(5),
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'inquiry_reference' => 'inq-expired-postpaid',
        'inquiry_expires_at' => now()->subMinute(),
        'payment_gateway_order_id' => 'INV-POSTPAID-EXPIRED',
        'digiflazz_ref_id' => 'PPOBREF-POSTPAID-EXPIRED',
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $result = $service->fulfillTransaction($transaction->uuid);

    expect($result->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_MANUAL_REVIEW);
    expect($result->fulfillment_message)->toContain('ditindaklanjuti manual');
    Http::assertNothingSent();
});

test('refresh transaction status updates unpaid transaction from midtrans status api', function () {
    $settlementTime = now()->format('Y-m-d H:i:s');

    Http::fake([
        'https://api.sandbox.midtrans.com/v2/PPOB-ORDER-REFRESH/status' => Http::response([
            'transaction_id' => 'midtrans-transaction-refresh',
            'order_id' => 'PPOB-ORDER-REFRESH',
            'gross_amount' => '12500.00',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
            'transaction_time' => $settlementTime,
            'settlement_time' => $settlementTime,
            'va_numbers' => [
                ['bank' => 'bri', 'va_number' => '1234567890'],
            ],
        ]),
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

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'provider_price' => 10000,
        'markup_amount' => 2500,
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'midtrans_order_id' => 'PPOB-ORDER-REFRESH',
        'payment_gateway_order_id' => 'PPOB-ORDER-REFRESH',
        'digiflazz_ref_id' => 'PPOBREF001',
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->refreshTransactionStatus($transaction->uuid);

    $transaction->refresh();

    expect($transaction->payment_status)->toBe(PpobTransaction::PAYMENT_PAID);
    expect($transaction->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_SUCCEEDED);
});

test('refresh transaction status marks refunded midtrans transactions as reversed without erasing payment history', function () {
    Http::fake([
        'https://api.sandbox.midtrans.com/v2/PPOB-ORDER-REVERSAL/status' => Http::response([
            'transaction_id' => 'midtrans-transaction-reversal',
            'order_id' => 'PPOB-ORDER-REVERSAL',
            'gross_amount' => '12500.00',
            'status_code' => '200',
            'transaction_status' => 'refund',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
        ]),
    ]);

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'provider_price' => 10000,
        'markup_amount' => 2500,
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'paid_at' => now()->subHour(),
        'fulfillment_status' => PpobTransaction::FULFILLMENT_SUCCEEDED,
        'midtrans_order_id' => 'PPOB-ORDER-REVERSAL',
        'payment_gateway_order_id' => 'PPOB-ORDER-REVERSAL',
        'digiflazz_ref_id' => 'PPOBREF-REVERSAL',
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->refreshTransactionStatus($transaction->uuid);

    $transaction->refresh();

    expect($transaction->payment_status)->toBe(PpobTransaction::PAYMENT_REVERSED);
    expect((float) $transaction->amount_received)->toBe(12500.0);
    expect($transaction->paid_at)->not()->toBeNull();
    expect(data_get($transaction->metadata, 'payment_reversal_status'))->toBe('refund');
    Http::assertSentCount(1);
});

test('refresh transaction status keeps querying tripay after local payment is already paid even when default gateway switches', function () {
    config()->set('services.ppob.payment_gateway', 'midtrans');

    Http::fake([
        'https://tripay.co.id/api/transaction/detail*' => Http::response([
            'data' => [
                'reference' => 'TRIPAY-REF-REVERSAL',
                'merchant_ref' => 'PPOB-TRIPAY-REVERSAL',
                'status' => 'FAILED',
                'payment_method' => 'BRI Virtual Account',
                'payment_method_code' => 'BRIVA',
                'total_amount' => 12500,
                'amount_received' => 0,
                'fee_customer' => 0,
                'fee_merchant' => 0,
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'provider_price' => 10000,
        'markup_amount' => 2500,
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'paid_at' => now()->subHour(),
        'fulfillment_status' => PpobTransaction::FULFILLMENT_SUCCEEDED,
        'payment_gateway_reference' => 'TRIPAY-REF-REVERSAL',
        'payment_gateway_order_id' => 'PPOB-TRIPAY-REVERSAL',
        'digiflazz_ref_id' => 'PPOBREF-TRIPAY-REVERSAL',
        'metadata' => [
            'payment_gateway' => 'tripay',
        ],
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->refreshTransactionStatus($transaction->uuid);

    expect($transaction->fresh()->payment_status)->toBe(PpobTransaction::PAYMENT_FAILED);
    Http::assertSentCount(1);
});

test('digiflazz callback is rejected when webhook secret is not configured', function () {
    config()->set('services.digiflazz.webhook_secret', '');

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
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
        'payment_gateway_order_id' => 'INV-001-DIGI',
        'digiflazz_ref_id' => 'PPOBREF-DIGI',
    ]);

    $payload = json_encode([
        'data' => [
            'ref_id' => 'PPOBREF-DIGI',
            'status' => 'Sukses',
            'message' => 'Transaksi Sukses',
            'sn' => 'SN-123',
        ],
    ], JSON_THROW_ON_ERROR);

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/digiflazz',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE' => 'sha1=fake-signature',
            'HTTP_X_DIGIFLAZZ_EVENT' => 'transaction.update',
        ],
        $payload,
    );

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid signature.',
        ]);

    expect($transaction->fresh()->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_PENDING);
});

test('digiflazz ping callback is acknowledged when signature is valid', function () {
    $payload = json_encode([
        'sed' => 'AgXXtVAHp',
        'hook_id' => '11aaabbb',
        'hook' => [
            'url' => 'https://example.com/api/ppob/callbacks/digiflazz',
            'secret' => 'digiflazz-secret',
            'type' => 'application/json',
            'status' => 1,
        ],
    ], JSON_THROW_ON_ERROR);

    $signature = 'sha1='.hash_hmac('sha1', $payload, 'digiflazz-secret');

    $response = $this->call(
        'POST',
        '/api/ppob/callbacks/digiflazz',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE' => $signature,
            'HTTP_X_DIGIFLAZZ_EVENT' => 'ping',
        ],
        $payload,
    );

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Ping received.',
        ]);
});

test('refresh transaction status skips digiflazz polling within cooldown window', function () {
    Http::fake();

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'provider_price' => 10000,
        'markup_amount' => 2500,
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PROCESSING,
        'payment_gateway_order_id' => 'INV-COOLDOWN',
        'digiflazz_ref_id' => 'PPOBREF-COOLDOWN',
        'updated_at' => now(),
        'created_at' => now()->subMinute(),
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->refreshTransactionStatus($transaction->uuid);

    Http::assertNothingSent();
});

test('refresh transaction status skips digiflazz prepaid checks older than ninety days', function () {
    Http::fake();

    $user = User::factory()->create();
    $transaction = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'provider_price' => 10000,
        'markup_amount' => 2500,
        'base_price' => 12500,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 12500,
        'amount_received' => 12500,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PROCESSING,
        'payment_gateway_order_id' => 'INV-AGED-PREPAID',
        'digiflazz_ref_id' => 'PPOBREF-AGED-PREPAID',
        'created_at' => now()->subDays(91),
        'updated_at' => now()->subMinutes(2),
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $service->refreshTransactionStatus($transaction->uuid);

    expect($transaction->fresh()->fulfillment_status)->toBe(PpobTransaction::FULFILLMENT_PROCESSING);
    Http::assertNothingSent();
});

test('reconcile transactions includes recent paid transactions that were already fulfilled for gateway audits', function () {
    Bus::fake();
    config()->set('services.ppob.fulfillment_dispatch', 'queue');
    config()->set('services.ppob.reconcile_success_window_hours', 24);

    $user = User::factory()->create();

    $unpaid = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'product_name' => 'XL 10.000',
        'customer_no' => '081234567890',
        'base_price' => 10000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 10000,
        'amount_received' => 0,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_UNPAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
        'payment_gateway_order_id' => 'INV-RECON-UNPAID',
        'digiflazz_ref_id' => 'PPOBREF-RECON-UNPAID',
    ]);

    $recentlySucceeded = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL20',
        'product_name' => 'XL 20.000',
        'customer_no' => '081234567891',
        'base_price' => 20000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 20000,
        'amount_received' => 20000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'paid_at' => now()->subMinutes(30),
        'fulfillment_status' => PpobTransaction::FULFILLMENT_SUCCEEDED,
        'midtrans_order_id' => 'PPOB-RECON-SUCCEEDED',
        'payment_gateway_order_id' => 'INV-RECON-SUCCEEDED',
        'digiflazz_ref_id' => 'PPOBREF-RECON-SUCCEEDED',
    ]);

    $staleSucceeded = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL25',
        'product_name' => 'XL 25.000',
        'customer_no' => '081234567895',
        'base_price' => 25000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 25000,
        'amount_received' => 25000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'paid_at' => now()->subDays(2),
        'fulfillment_status' => PpobTransaction::FULFILLMENT_SUCCEEDED,
        'midtrans_order_id' => 'PPOB-RECON-STALE',
        'payment_gateway_order_id' => 'INV-RECON-STALE',
        'digiflazz_ref_id' => 'PPOBREF-RECON-STALE',
    ]);

    $needsFollowUp = PpobTransaction::query()->create([
        'user_id' => $user->id,
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL30',
        'product_name' => 'XL 30.000',
        'customer_no' => '081234567892',
        'base_price' => 30000,
        'fee_customer' => 0,
        'fee_merchant' => 0,
        'total_amount' => 30000,
        'amount_received' => 30000,
        'payment_channel_code' => 'BRIVA',
        'payment_channel_name' => 'BRI Virtual Account',
        'payment_status' => PpobTransaction::PAYMENT_PAID,
        'fulfillment_status' => PpobTransaction::FULFILLMENT_PROCESSING,
        'payment_gateway_order_id' => 'INV-RECON-PROCESSING',
        'digiflazz_ref_id' => 'PPOBREF-RECON-PROCESSING',
    ]);

    /** @var PpobTransactionService $service */
    $service = app(PpobTransactionService::class);
    $count = $service->reconcileTransactions(10);

    expect($count)->toBe(3);

    Bus::assertDispatched(ReconcilePpobTransaction::class, function (ReconcilePpobTransaction $job) use ($unpaid): bool {
        return $job->transactionUuid === $unpaid->uuid;
    });

    Bus::assertDispatched(ReconcilePpobTransaction::class, function (ReconcilePpobTransaction $job) use ($recentlySucceeded): bool {
        return $job->transactionUuid === $recentlySucceeded->uuid;
    });

    Bus::assertDispatched(ReconcilePpobTransaction::class, function (ReconcilePpobTransaction $job) use ($needsFollowUp): bool {
        return $job->transactionUuid === $needsFollowUp->uuid;
    });

    Bus::assertNotDispatched(ReconcilePpobTransaction::class, function (ReconcilePpobTransaction $job) use ($staleSucceeded): bool {
        return $job->transactionUuid === $staleSucceeded->uuid;
    });
});
