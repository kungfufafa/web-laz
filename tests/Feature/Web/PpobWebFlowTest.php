<?php

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.midtrans.server_key', 'midtrans-server-key');
    config()->set('services.midtrans.client_key', 'midtrans-client-key');
    config()->set('services.midtrans.merchant_id', 'G123456789');
    config()->set('services.midtrans.is_production', false);
    config()->set('services.midtrans.enabled_payments', ['bri_va', 'qris']);
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

test('home displays ppob services', function (): void {
    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'Telkomsel',
        'type' => 'Umum',
        'product_name' => 'Pulsa Telkomsel 10.000',
        'buyer_sku_code' => 'TS10',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 11000,
        'markup_amount' => 1000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Isi Pulsa')
        ->assertSee('grid-cols-6', false)
        ->assertDontSee('Transaksi PPOB via Web');
});

test('guest can register through web and access ppob page', function (): void {
    $response = $this->post('/register', [
        'name' => 'Web User',
        'phone' => '+6281234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('ppob.index'));
    $this->assertAuthenticated();

    expect(User::query()->where('phone', '081234567890')->exists())->toBeTrue();
});

test('ppob route redirects to home for authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/ppob')
        ->assertRedirect(route('home'));
});

test('ppob route redirects to home for guest user', function (): void {
    $this->get('/ppob')
        ->assertRedirect(route('home'));
});

test('login and register pages use the public ppob web layout', function (): void {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Lanjutkan PPOB')
        ->assertSee('Masuk ke PPOB Web')
        ->assertSee('Pulsa')
        ->assertSee('Kebijakan Privasi');

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Mulai PPOB')
        ->assertSee('Daftar dan mulai transaksi')
        ->assertSee('Pulsa')
        ->assertSee('Kebijakan Privasi');
});

test('ppob web page groups products into familiar journeys', function (): void {
    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Pulsa',
        'brand' => 'Telkomsel',
        'type' => 'Umum',
        'product_name' => 'Pulsa Telkomsel 10.000',
        'buyer_sku_code' => 'TS10',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 11000,
        'markup_amount' => 1000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Data',
        'brand' => 'Indosat',
        'type' => 'Internet',
        'product_name' => 'Data Indosat 2GB',
        'buyer_sku_code' => 'ISDATA2',
        'price' => 12000,
        'provider_price' => 12000,
        'sell_price' => 13500,
        'markup_amount' => 1500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'PLN',
        'brand' => 'PLN',
        'type' => 'Token Listrik',
        'product_name' => 'Token PLN 20.000',
        'buyer_sku_code' => 'PLN20',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 21000,
        'markup_amount' => 1000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

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

    $this->actingAs($user)->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Isi Pulsa')
        ->assertSee('Paket Data')
        ->assertSee('Token PLN')
        ->assertSee('Tagihan PLN');
});

test('ppob web catalog still exposes journeys beyond the first 120 active products', function (): void {
    collect(range(1, 120))->each(function (int $index): void {
        PpobProduct::query()->create([
            'provider' => 'digiflazz',
            'service_type' => 'prepaid',
            'category' => 'Pulsa',
            'brand' => 'Telkomsel',
            'type' => 'Umum',
            'product_name' => sprintf('Pulsa Telkomsel %03d', $index),
            'buyer_sku_code' => sprintf('TS%03d', $index),
            'price' => 10000,
            'provider_price' => 10000,
            'sell_price' => 11000,
            'markup_amount' => 1000,
            'buyer_product_status' => true,
            'seller_product_status' => true,
        ]);
    });

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

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Voucher');

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'voucher',
    ]))
        ->assertSuccessful()
        ->assertSee('ML 86 Diamonds');
});

test('authenticated user can create prepaid ppob transaction from web', function (): void {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-web',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-web',
        ]),
    ]);

    $user = User::factory()->create([
        'phone' => '081234567890',
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
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $catalogResponse = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]));

    $catalogResponse->assertSuccessful()
        ->assertSee('XL');

    $response = $this->actingAs($user)->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ]);

    $transaction = PpobTransaction::query()->first();

    expect($transaction)->not()->toBeNull();

    $response->assertRedirect(route('ppob.transactions.show', $transaction));

    $this->get(route('ppob.transactions.show', $transaction))
        ->assertSuccessful()
        ->assertSee('Bayar sekarang')
        ->assertDontSee('Buka checkout')
        ->assertDontSee('Pembayaran UNPAID')
        ->assertDontSee('Fulfillment PENDING')
        ->assertSee('snap-token-web')
        ->assertSee('Menunggu pembayaran')
        ->assertSee('Rincian transaksi')
        ->assertSee('Kebijakan Privasi');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    });
});

test('prepaid pulsa or data transaction rejects short mobile numbers', function (): void {
    Http::fake();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'Data',
        'brand' => 'TRI',
        'type' => 'Internet',
        'product_name' => 'Tri Data 1 GB',
        'buyer_sku_code' => 'TRI1GB',
        'price' => 10000,
        'provider_price' => 10000,
        'sell_price' => 11500,
        'markup_amount' => 1500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $catalogRoute = route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'data',
    ]);

    $response = $this->from($catalogRoute)->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'data',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'TRI1GB',
        'customer_no' => '0895',
        'payment_channel_code' => 'BRIVA',
    ]);

    $response->assertRedirect($catalogRoute)
        ->assertSessionHasErrors(['customer_no']);

    expect(PpobTransaction::query()->count())->toBe(0);

    Http::assertNothingSent();
});

test('prepaid pln transaction rejects malformed customer identifiers', function (): void {
    Http::fake();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'PLN',
        'brand' => 'PLN',
        'type' => 'Token Listrik',
        'product_name' => 'Token PLN 20.000',
        'buyer_sku_code' => 'PLN20',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 21000,
        'markup_amount' => 1000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $catalogRoute = route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pln_token',
    ]);

    $response = $this->from($catalogRoute)->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'pln_token',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'PLN20',
        'customer_no' => 'abc<script>',
        'payment_channel_code' => 'BRIVA',
    ]);

    $response->assertRedirect($catalogRoute)
        ->assertSessionHasErrors(['customer_no']);

    expect(PpobTransaction::query()->count())->toBe(0);

    Http::assertNothingSent();
});

test('prepaid catalog no longer shows operator chooser copy', function (): void {
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

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]))
        ->assertSuccessful()
        ->assertDontSee('Operator')
        ->assertDontSee('Pembelian langsung')
        ->assertDontSee('Ringkasan pembelian')
        ->assertDontSee('Cara transaksi')
        ->assertDontSee('Kembali ke beranda')
        ->assertSee('Nomor HP')
        ->assertSee('Masukkan nomor HP')
        ->assertSee('Beli sekarang')
        ->assertSee('Kebijakan Privasi')
        ->assertSee('Syarat & Ketentuan', false)
        ->assertSee('Bantuan');
});

test('token pln catalog uses customer id copy instead of phone copy', function (): void {
    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'prepaid',
        'category' => 'PLN',
        'brand' => 'PLN',
        'type' => 'Token Listrik',
        'product_name' => 'Token PLN 20.000',
        'buyer_sku_code' => 'PLN20',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 21000,
        'markup_amount' => 1000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pln_token',
    ]))
        ->assertSuccessful()
        ->assertSee('ID Pelanggan / No. Meter')
        ->assertDontSee('Nomor HP / ID');
});

test('voucher catalog uses game id copy instead of phone copy', function (): void {
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

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'voucher',
    ]))
        ->assertSuccessful()
        ->assertSee('User ID / Zone ID')
        ->assertDontSee('Nomor HP / ID');
});

test('valid prepaid journey with zero search matches still renders successfully', function (): void {
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

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
        'search' => 'zzzz-not-found',
    ]))
        ->assertSuccessful()
        ->assertSee('Isi Pulsa')
        ->assertSee('zzzz-not-found');
});

test('guest can create prepaid ppob transaction from web', function (): void {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-guest-web',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-guest-web',
        ]),
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
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->get(route('ppob.catalog', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]))
        ->assertSuccessful()
        ->assertSee('XL');

    $response = $this->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ]);

    $transaction = PpobTransaction::query()->first();

    expect($transaction)->not()->toBeNull();
    expect($transaction?->user)->not()->toBeNull();
    expect($transaction?->user?->name)->toBe('Guest PPOB');

    $response->assertRedirect(route('ppob.transactions.show', $transaction));

    $this->get(route('ppob.transactions.show', $transaction))
        ->assertSuccessful()
        ->assertSee('snap-token-guest-web');
});

test('guest transaction becomes inaccessible after logging into another account in the same session', function (): void {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-guest-login',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-guest-login',
        ]),
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
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ])->assertRedirect();

    $guestTransaction = PpobTransaction::query()->first();

    expect($guestTransaction)->not()->toBeNull();
    expect($guestTransaction?->user?->name)->toBe('Guest PPOB');

    $member = User::factory()->create([
        'phone' => '081298765432',
        'password' => 'password123',
    ]);

    $this->post(route('login.store'), [
        'phone' => $member->phone,
        'password' => 'password123',
    ])->assertRedirect(route('ppob.index'));

    $this->get(route('ppob.transactions.show', $guestTransaction))
        ->assertNotFound();
});

test('postpaid inquiry is only shown on the matching journey page', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::response([
            'data' => [
                'ref_id' => 'PPOBPOSTPAID001',
                'customer_no' => '1234567890',
                'customer_name' => 'Pelanggan PLN',
                'buyer_sku_code' => 'PLNPOST',
                'message' => 'Inquiry berhasil',
                'status' => 'Sukses',
                'price' => 20000,
                'selling_price' => 20000,
            ],
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'Listrik',
        'brand' => 'PLN',
        'type' => 'Tagihan',
        'product_name' => 'PLN Pascabayar',
        'buyer_sku_code' => 'PLNPOST',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 22000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'BPJS',
        'brand' => 'BPJS',
        'type' => 'Tagihan',
        'product_name' => 'BPJS Kesehatan',
        'buyer_sku_code' => 'BPJSPOST',
        'price' => 15000,
        'provider_price' => 15000,
        'sell_price' => 17000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->actingAs($user)->post(route('ppob.catalog.inquiries.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'buyer_sku_code' => 'PLNPOST',
        'customer_no' => '1234567890',
    ])->assertRedirect(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'bpjs',
    ]))
        ->assertSuccessful()
        ->assertViewHas('latestInquiry', null)
        ->assertSee('Cek tagihan')
        ->assertDontSee('Lanjut bayar')
        ->assertDontSee('Pelanggan PLN');
});

test('postpaid inquiry remains available after reloading the same catalog page', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::response([
            'data' => [
                'ref_id' => 'PPOBPOSTPAIDRELOAD',
                'customer_no' => '1234567890',
                'customer_name' => 'Pelanggan PLN',
                'buyer_sku_code' => 'PLNPOST',
                'message' => 'Inquiry berhasil',
                'status' => 'Sukses',
                'price' => 20000,
                'selling_price' => 20000,
            ],
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'Listrik',
        'brand' => 'PLN',
        'type' => 'Tagihan',
        'product_name' => 'PLN Pascabayar',
        'buyer_sku_code' => 'PLNPOST',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 22000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->actingAs($user)->post(route('ppob.catalog.inquiries.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'buyer_sku_code' => 'PLNPOST',
        'customer_no' => '1234567890',
    ])->assertRedirect(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $firstCatalogView = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $firstCatalogView->assertSuccessful()
        ->assertSee('Pelanggan PLN')
        ->assertSee('Lanjut bayar');

    $secondCatalogView = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $secondCatalogView->assertSuccessful()
        ->assertSee('Pelanggan PLN')
        ->assertSee('Lanjut bayar');

    expect(is_array($secondCatalogView->viewData('latestInquiry')))->toBeTrue();
});

test('guest transaction becomes inaccessible after registering in the same session', function (): void {
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-guest-register',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-guest-register',
        ]),
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
        'provider_price' => 10000,
        'sell_price' => 12500,
        'markup_amount' => 2500,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'prepaid',
        'journey' => 'pulsa',
    ]), [
        'service_type' => 'prepaid',
        'buyer_sku_code' => 'XL10',
        'customer_no' => '081234567890',
        'payment_channel_code' => 'BRIVA',
    ])->assertRedirect();

    $guestTransaction = PpobTransaction::query()->first();

    expect($guestTransaction)->not()->toBeNull();
    expect($guestTransaction?->user?->name)->toBe('Guest PPOB');

    $this->post(route('register.store'), [
        'name' => 'Web User Baru',
        'phone' => '081277788899',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('ppob.index'));

    $this->get(route('ppob.transactions.show', $guestTransaction))
        ->assertNotFound();
});

test('authenticated user can inquire postpaid and continue payment from web', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::sequence()
            ->push([
                'data' => [
                    'ref_id' => 'PPOBPOSTPAID001',
                    'customer_no' => '1234567890',
                    'customer_name' => 'Pelanggan PLN',
                    'buyer_sku_code' => 'PLNPOST',
                    'message' => 'Inquiry berhasil',
                    'status' => 'Sukses',
                    'price' => 20000,
                    'selling_price' => 20000,
                ],
            ])
            ->push([
                'data' => [
                    'ref_id' => 'PPOBPOSTPAID001',
                    'customer_no' => '1234567890',
                    'customer_name' => 'Pelanggan PLN',
                    'buyer_sku_code' => 'PLNPOST',
                    'message' => 'Inquiry berhasil',
                    'status' => 'Sukses',
                    'price' => 20000,
                    'selling_price' => 20000,
                ],
            ]),
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-postpaid',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-postpaid',
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'Listrik',
        'brand' => 'PLN',
        'type' => 'Tagihan',
        'product_name' => 'PLN Pascabayar',
        'buyer_sku_code' => 'PLNPOST',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 22000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $catalogResponse = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $catalogResponse->assertSuccessful()
        ->assertSee('PLN');

    $inquiryResponse = $this->actingAs($user)->post(route('ppob.catalog.inquiries.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'buyer_sku_code' => 'PLNPOST',
        'customer_no' => '1234567890',
    ]);

    $inquiryResponse->assertRedirect(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $catalogAfterInquiry = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));
    $catalogAfterInquiry->assertSuccessful()
        ->assertSee('Pelanggan PLN')
        ->assertSee('Lanjut bayar');

    $inquiry = $catalogAfterInquiry->viewData('latestInquiry');

    expect(is_array($inquiry))->toBeTrue();

    $transactionResponse = $this->actingAs($user)->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'service_type' => 'postpaid',
        'inquiry_reference' => $inquiry['reference'],
        'payment_channel_code' => 'BRIVA',
    ]);

    $transaction = PpobTransaction::query()->first();

    expect($transaction)->not()->toBeNull();
    expect($transaction?->inquiry_reference)->toBe($inquiry['reference']);

    $transactionResponse->assertRedirect(route('ppob.transactions.show', $transaction));
});

test('postpaid inquiry is cleared from the catalog after checkout succeeds', function (): void {
    Http::fake([
        'https://api.digiflazz.com/v1/transaction' => Http::response([
            'data' => [
                'ref_id' => 'PPOBPOSTPAID002',
                'customer_no' => '1234567890',
                'customer_name' => 'Pelanggan PLN',
                'buyer_sku_code' => 'PLNPOST',
                'message' => 'Inquiry berhasil',
                'status' => 'Sukses',
                'price' => 20000,
                'selling_price' => 20000,
            ],
        ]),
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-postpaid-clear',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-postpaid-clear',
        ]),
    ]);

    $user = User::factory()->create();

    PpobProduct::query()->create([
        'provider' => 'digiflazz',
        'service_type' => 'postpaid',
        'category' => 'Listrik',
        'brand' => 'PLN',
        'type' => 'Tagihan',
        'product_name' => 'PLN Pascabayar',
        'buyer_sku_code' => 'PLNPOST',
        'price' => 20000,
        'provider_price' => 20000,
        'sell_price' => 22000,
        'markup_amount' => 2000,
        'buyer_product_status' => true,
        'seller_product_status' => true,
    ]);

    $this->actingAs($user)->post(route('ppob.catalog.inquiries.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'buyer_sku_code' => 'PLNPOST',
        'customer_no' => '1234567890',
    ])->assertRedirect(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $catalogAfterInquiry = $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]));

    $catalogAfterInquiry->assertSuccessful()
        ->assertSee('Lanjut bayar')
        ->assertSee('Pelanggan PLN');

    $inquiry = $catalogAfterInquiry->viewData('latestInquiry');

    expect(is_array($inquiry))->toBeTrue();

    $this->actingAs($user)->post(route('ppob.catalog.transactions.store', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]), [
        'service_type' => 'postpaid',
        'inquiry_reference' => $inquiry['reference'],
        'payment_channel_code' => 'BRIVA',
    ])->assertRedirect();

    $this->actingAs($user)->get(route('ppob.catalog', [
        'serviceType' => 'postpaid',
        'journey' => 'pln_bill',
    ]))
        ->assertSuccessful()
        ->assertSee('Cek tagihan')
        ->assertDontSee('Lanjut bayar')
        ->assertDontSee('Pelanggan PLN');
});
