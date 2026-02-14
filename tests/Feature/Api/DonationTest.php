<?php

use App\Models\Donation;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can retrieve active payment methods', function () {
    PaymentMethod::factory()->create([
        'name' => 'Bank BCA',
        'is_active' => true,
    ]);
    PaymentMethod::factory()->create([
        'name' => 'Inactive Bank',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/payment-methods');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['name' => 'Bank BCA'])
        ->assertJsonMissing(['name' => 'Inactive Bank']);
});

test('can retrieve donation flow configuration', function () {
    $response = $this->getJson('/api/donation-config');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'categories',
            'contexts',
            'zakat' => ['calculator_types', 'defaults'],
            'recommended_amounts',
        ]);
});

test('can calculate zakat fitrah amount', function () {
    $response = $this->postJson('/api/zakat/calculate', [
        'type' => 'fitrah',
        'people_count' => 4,
        'rice_price_per_kg' => 18000,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('type', 'fitrah')
        ->assertJsonPath('recommended_amount', 180000)
        ->assertJsonPath('is_obligatory', true);
});

test('can create a donation', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    $file = UploadedFile::fake()->image('proof.jpg');

    $response = $this->actingAs($user)->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'infak',
        'payment_type' => 'umum',
        'context_slug' => 'infak-operasional',
        'context_label' => 'Infak Operasional Dakwah',
        'payment_method_id' => $paymentMethod->id,
        'proof_image' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'amount',
                'category',
                'payment_type',
                'status',
                'payment_method_name',
                'created_at',
            ],
        ]);

    $donation = Donation::first();
    expect($donation->amount)->toEqual(50000);
    expect($donation->category)->toEqual('infak');
    expect($donation->payment_type)->toEqual('umum');
    expect($donation->status)->toEqual('pending');
    expect($donation->user_id)->toEqual($user->id);

    Storage::disk('public')->assertExists($donation->proof_image);
});

test('donation creation validation fails', function () {
    $response = $this->postJson('/api/donations', [
        'amount' => 5000,
        'payment_method_id' => 999999,
        'category' => 'zakat',
        'payment_type' => 'umum',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'payment_method_id', 'guest_token', 'donor_name', 'donor_phone']);
});

test('can create a donation without proof image', function () {
    $user = User::factory()->create();
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->actingAs($user)->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'zakat',
        'payment_type' => 'maal',
        'calculator_type' => 'maal',
        'calculator_breakdown' => [
            'net_assets' => 150000000,
            'nisab_amount' => 95000000,
            'rate' => 0.025,
        ],
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.category', 'zakat')
        ->assertJsonPath('data.payment_type', 'maal');

    $donation = Donation::latest('created_at')->first();
    expect($donation)->not()->toBeNull();
    expect($donation->proof_image)->toBeNull();
});

test('guest can create a donation without login', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/donations', [
        'amount' => 75000,
        'category' => 'sedekah',
        'payment_type' => 'jariyah',
        'context_slug' => 'sedekah-jariyah',
        'context_label' => 'Sedekah Jariyah',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-123',
        'donor_name' => 'Guest User',
        'donor_phone' => '08123456789',
        'donor_email' => 'guest@example.com',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.is_guest', true)
        ->assertJsonPath('data.category', 'sedekah')
        ->assertJsonPath('data.payment_type', 'jariyah');

    $donation = Donation::latest('created_at')->first();
    expect($donation)->not()->toBeNull();
    expect($donation->user_id)->toBeNull();
    expect($donation->guest_token)->toBe('guest-token-123');
});

test('sodaqoh alias is normalized to sedekah', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'sodaqoh',
        'payment_type' => 'jariyah',
        'context_slug' => 'sedekah-umum',
        'context_label' => 'Sedekah Umum',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-alias',
        'donor_name' => 'Guest Alias',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.category', 'sedekah')
        ->assertJsonPath('data.payment_type', 'jariyah');

    $donation = Donation::latest('created_at')->first();
    expect($donation)->not()->toBeNull();
    expect($donation->category)->toBe('sedekah');
});

test('qris donation returns dynamic payload based on amount', function () {
    $paymentMethod = PaymentMethod::factory()->create([
        'is_active' => true,
        'type' => 'qris',
        'qris_static_payload' => '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18',
    ]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'infak',
        'payment_type' => 'umum',
        'context_slug' => 'infak-kemanusiaan',
        'context_label' => 'Infak Kemanusiaan',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-555',
        'donor_name' => 'Guest QRIS',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(201);

    $payload = $response->json('data.qris_payload');
    expect($payload)->toBeString();
    expect($payload)->toContain('010212');
    expect($payload)->toContain('540550000');
    expect(substr($payload, -8, 4))->toBe('6304');
});

test('qris donation fails when static payload is not configured', function () {
    $paymentMethod = PaymentMethod::factory()->create([
        'is_active' => true,
        'type' => 'qris',
        'qris_static_payload' => null,
    ]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'infak',
        'payment_type' => 'umum',
        'context_slug' => 'infak-kemanusiaan',
        'context_label' => 'Infak Kemanusiaan',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-777',
        'donor_name' => 'Guest QRIS',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method_id']);
});

test('payment type must match donation category', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'zakat',
        'payment_type' => 'umum',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-999',
        'donor_name' => 'Guest User',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_type']);
});

test('infak and sedekah require a donation context', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'infak',
        'payment_type' => 'umum',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-ctx',
        'donor_name' => 'Guest User',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['context_label']);
});

test('infak cannot use sedekah payment type', function () {
    $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/donations', [
        'amount' => 50000,
        'category' => 'infak',
        'payment_type' => 'jariyah',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-token-1001',
        'donor_name' => 'Guest User',
        'donor_phone' => '08123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_type']);
});

test('can retrieve user donation history', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $paymentMethod = PaymentMethod::factory()->create();

    $myDonation = Donation::factory()->create([
        'user_id' => $user->id,
        'payment_method_id' => $paymentMethod->id,
        'created_at' => now(),
    ]);

    $oldDonation = Donation::factory()->create([
        'user_id' => $user->id,
        'payment_method_id' => $paymentMethod->id,
        'created_at' => now()->subDay(),
    ]);

    $otherDonation = Donation::factory()->create([
        'user_id' => $otherUser->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/donations/history');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $myDonation->uuid])
        ->assertJsonFragment(['id' => $oldDonation->uuid])
        ->assertJsonMissing(['id' => $otherDonation->uuid]);

    $data = $response->json('data');
    expect($data[0]['id'])->toBe($myDonation->uuid);
    expect($data[1]['id'])->toBe($oldDonation->uuid);
});

test('guest can retrieve donation history by guest token', function () {
    $paymentMethod = PaymentMethod::factory()->create();

    $myGuestDonation = Donation::factory()->create([
        'user_id' => null,
        'guest_token' => 'guest-token-abc',
        'payment_method_id' => $paymentMethod->id,
        'created_at' => now(),
    ]);

    $otherGuestDonation = Donation::factory()->create([
        'user_id' => null,
        'guest_token' => 'guest-token-other',
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response = $this->getJson('/api/donations/history?guest_token=guest-token-abc');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $myGuestDonation->uuid])
        ->assertJsonMissing(['id' => $otherGuestDonation->uuid]);
});
