<?php

use App\Models\PpobPricingRule;
use App\Models\PpobTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('services.digiflazz.username', 'digiflazz-user');
    config()->set('services.digiflazz.api_key', 'digiflazz-key');
    config()->set('services.digiflazz.webhook_secret', 'digiflazz-webhook-secret');
    config()->set('services.midtrans.server_key', 'midtrans-server-key');
    config()->set('services.midtrans.client_key', 'midtrans-client-key');
    config()->set('services.ppob.payment_gateway', 'midtrans');
    config()->set('services.tripay.api_key', 'tripay-api-key');
    config()->set('services.tripay.private_key', 'tripay-private-key');
    config()->set('services.tripay.merchant_code', 'T0001');
});

test('ppob health check succeeds when required config is present', function (): void {
    PpobPricingRule::query()->create([
        'name' => 'Default PPOB Prepaid',
        'service_type' => 'prepaid',
        'markup_type' => 'fixed',
        'markup_value' => 1500,
        'rounding_unit' => 100,
        'priority' => 100,
        'is_active' => true,
    ]);

    $this->artisan('ppob:health-check')
        ->assertExitCode(0);
});

test('ppob health check fails when digiflazz webhook secret is missing', function (): void {
    config()->set('services.digiflazz.webhook_secret', null);

    $this->artisan('ppob:health-check')
        ->assertExitCode(1);
});

test('ppob health check fails when active tripay config is missing', function (): void {
    config()->set('services.ppob.payment_gateway', 'tripay');
    config()->set('services.tripay.private_key', null);

    $this->artisan('ppob:health-check')
        ->assertExitCode(1);
});

test('ppob health check does not require queue tables for sync dispatch or non-database queues', function (): void {
    PpobPricingRule::query()->create([
        'name' => 'Default PPOB Prepaid',
        'service_type' => 'prepaid',
        'markup_type' => 'fixed',
        'markup_value' => 1500,
        'rounding_unit' => 100,
        'priority' => 100,
        'is_active' => true,
    ]);

    config()->set('services.ppob.fulfillment_dispatch', 'sync');
    config()->set('queue.default', 'redis');
    config()->set('queue.failed.driver', 'file');

    Schema::dropIfExists('jobs');
    Schema::dropIfExists('failed_jobs');

    $this->artisan('ppob:health-check')
        ->assertExitCode(0);
});

test('ppob monitor failures reports threshold reached when repeated failures exist', function (): void {
    foreach (range(1, 3) as $index) {
        PpobTransaction::query()->create([
            'user_id' => User::factory()->create()->id,
            'provider' => 'digiflazz',
            'service_type' => 'prepaid',
            'buyer_sku_code' => 'XL'.$index,
            'product_name' => 'XL '.$index,
            'customer_no' => '08123456789'.$index,
            'provider_price' => 10000,
            'markup_amount' => 1500,
            'base_price' => 11500,
            'fee_customer' => 0,
            'fee_merchant' => 0,
            'total_amount' => 11500,
            'amount_received' => 11500,
            'payment_channel_code' => 'BRIVA',
            'payment_channel_name' => 'BRI Virtual Account',
            'payment_status' => PpobTransaction::PAYMENT_FAILED,
            'fulfillment_status' => PpobTransaction::FULFILLMENT_FAILED,
            'payment_gateway_order_id' => 'INV-'.$index,
            'digiflazz_ref_id' => 'PPOBREF-'.$index,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    $this->artisan('ppob:monitor-failures', [
        '--minutes' => 15,
        '--threshold' => 3,
    ])
        ->expectsOutputToContain('PPOB alert threshold reached.')
        ->assertExitCode(0);
});
