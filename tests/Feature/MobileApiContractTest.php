<?php

use App\Models\Article;
use App\Models\Donation;
use App\Models\MemberPrayer;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('mobile auth contract exposes register profile and logout payloads', function () {
    $registerResponse = $this->postJson('/api/register', [
        'name' => 'Mobile Contract User',
        'email' => 'mobile-contract@example.com',
        'phone' => '081234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $registerResponse->assertCreated()
        ->assertJsonStructure([
            'access_token',
            'token_type',
        ]);

    expect($registerResponse->json('access_token'))->toBeString()->not->toBe('');
    expect($registerResponse->json('token_type'))->toBe('Bearer');

    $token = $registerResponse->json('access_token');

    $profileResponse = $this->getJson('/api/user', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $profileResponse->assertOk()
        ->assertJsonStructure(mobileUserContract());

    expect($profileResponse->json('name'))->toBe('Mobile Contract User');
    expect($profileResponse->json('email'))->toBe('mobile-contract@example.com');
    expect($profileResponse->json('phone'))->toBe('081234567890');
    expect($profileResponse->json('avatar_url'))->toBeNull();

    $logoutResponse = $this->postJson('/api/logout', [], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $logoutResponse->assertOk()
        ->assertJsonStructure([
            'message',
        ]);
});

test('mobile auth contract exposes login payloads for existing users', function () {
    User::factory()->create([
        'name' => 'Existing Mobile User',
        'email' => 'existing-mobile@example.com',
        'phone' => '081298765432',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'phone' => '+6281298765432',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'access_token',
            'token_type',
        ]);

    expect($response->json('access_token'))->toBeString()->not->toBe('');
    expect($response->json('token_type'))->toBe('Bearer');
});

test('mobile content contract exposes article collection and detail payloads', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/mobile-contract-cover.jpg', 'cover-content');

    $article = Article::factory()->create([
        'title' => 'Artikel Mobile Contract',
        'slug' => 'artikel-mobile-contract',
        'content' => 'Konten artikel untuk contract test mobile.',
        'thumbnail' => 'articles/mobile-contract-cover.jpg',
        'is_published' => true,
    ]);

    $collectionResponse = $this->getJson('/api/articles');

    assertPaginatedContract($collectionResponse, mobileArticleContract());
    expect($collectionResponse->json('data.0.slug'))->toBe($article->slug);
    expect($collectionResponse->json('data.0.thumbnail'))->toEndWith('/storage/articles/mobile-contract-cover.jpg');
    expect($collectionResponse->json('data.0.published_at'))->toBeString();

    $detailResponse = $this->getJson("/api/articles/{$article->slug}");

    $detailResponse->assertOk()
        ->assertJsonStructure([
            'data' => mobileArticleContract(),
        ]);

    expect($detailResponse->json('data.slug'))->toBe('artikel-mobile-contract');
    expect($detailResponse->json('data.content'))->toBe('Konten artikel untuk contract test mobile.');
});

test('mobile content contract exposes video collection payloads', function () {
    Storage::fake('public');
    Storage::disk('public')->put('videos/mobile-contract-cover.jpg', 'cover-content');

    Video::factory()->create([
        'title' => 'Video Mobile Contract',
        'youtube_id' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'description' => 'Deskripsi video untuk contract test mobile.',
        'thumbnail' => 'videos/mobile-contract-cover.jpg',
        'is_published' => true,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/videos');

    assertPaginatedContract($response, mobileVideoContract());
    expect($response->json('data.0.youtube_id'))->toBe('dQw4w9WgXcQ');
    expect($response->json('data.0.youtube_url'))->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    expect($response->json('data.0.youtube_embed_url'))->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
    expect($response->json('data.0.thumbnail'))->toEndWith('/storage/videos/mobile-contract-cover.jpg');
});

test('mobile prayers contract exposes public list payloads', function () {
    Storage::fake('local');
    Storage::disk('local')->put('avatars/mobile-prayer-avatar.png', 'avatar-content');

    $user = User::factory()->create([
        'name' => 'Doa Mobile User',
        'avatar_url' => 'avatars/mobile-prayer-avatar.png',
    ]);

    MemberPrayer::factory()->create([
        'user_id' => $user->id,
        'content' => 'Doa untuk contract test mobile.',
        'is_anonymous' => false,
        'likes_count' => 7,
        'status' => 'published',
    ]);

    $response = $this->getJson('/api/prayers');

    assertPaginatedContract($response, mobilePrayerContract());
    expect($response->json('data.0.user.name'))->toBe('Doa Mobile User');
    expect($response->json('data.0.user.avatar_url'))->toEndWith('/api/media/avatars/mobile-prayer-avatar.png');
    expect($response->json('data.0.user_name'))->toBe('Doa Mobile User');
    expect($response->json('data.0.is_liked_by_me'))->toBeFalse();
});

test('mobile prayers contract exposes authenticated list store and amen payloads', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile-contract')->plainTextToken;

    $existingPrayer = MemberPrayer::factory()->create([
        'content' => 'Doa existing yang sudah diaminkan.',
        'is_anonymous' => false,
        'likes_count' => 1,
        'status' => 'published',
    ]);
    $existingPrayer->supports()->attach($user->id);

    $listResponse = $this->getJson('/api/prayers', [
        'Authorization' => 'Bearer '.$token,
    ]);

    assertPaginatedContract($listResponse, mobilePrayerContract());
    expect($listResponse->json('data.0.is_liked_by_me'))->toBeTrue();

    $storeResponse = $this->postJson('/api/prayers', [
        'content' => 'Doa baru dari mobile contract.',
        'is_anonymous' => true,
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $storeResponse->assertCreated()
        ->assertJsonStructure([
            'data' => mobilePrayerContract(),
        ]);

    expect($storeResponse->json('data.user'))->toBeNull();
    expect($storeResponse->json('data.user_name'))->toBe('Hamba Allah');

    $amenResponse = $this->postJson("/api/prayers/{$existingPrayer->id}/amen", [], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $amenResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'liked',
            'likes_count',
        ]);

    expect($amenResponse->json('success'))->toBeTrue();
    expect($amenResponse->json('liked'))->toBeFalse();
    expect($amenResponse->json('likes_count'))->toBe(0);
});

test('mobile donation contract exposes payment methods and donation config payloads', function () {
    Storage::fake('public');
    Storage::disk('public')->put('payment-methods/mobile-logo.png', 'logo-content');
    Storage::disk('public')->put('payment-methods/mobile-qris.png', 'qris-content');

    PaymentMethod::factory()->create([
        'name' => 'QRIS Mobile Contract',
        'type' => 'qris',
        'account_number' => '1234567890',
        'account_holder' => 'LAZ Mobile Contract',
        'logo' => 'payment-methods/mobile-logo.png',
        'qris_image' => 'payment-methods/mobile-qris.png',
        'qris_static_payload' => '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18',
        'is_active' => true,
    ]);

    $paymentMethodsResponse = $this->getJson('/api/payment-methods');

    $paymentMethodsResponse->assertOk()
        ->assertJsonStructure([
            'data' => [
                mobilePaymentMethodContract(),
            ],
        ]);

    expect($paymentMethodsResponse->json('data.0.logo_url'))->toEndWith('/storage/payment-methods/mobile-logo.png');
    expect($paymentMethodsResponse->json('data.0.qris_image_url'))->toEndWith('/storage/payment-methods/mobile-qris.png');
    expect($paymentMethodsResponse->json('data.0.qris_static_payload'))->toBeString();
    expect($paymentMethodsResponse->json('data.0.has_qris_template'))->toBeTrue();

    $configResponse = $this->getJson('/api/donation-config');

    $configResponse->assertOk()
        ->assertJsonStructure([
            'categories' => [[
                'key',
                'label',
                'description',
                'requires_context',
                'payment_types' => [[
                    'key',
                    'label',
                    'description',
                    'conditions',
                    'is_zakat_calculator',
                ]],
            ]],
            'contexts',
            'zakat' => [
                'calculator_types' => [[
                    'key',
                    'label',
                ]],
                'defaults' => [
                    'fitrah_rice_kg_per_person',
                    'maal_nisab_gold_grams',
                    'profesi_nisab_gold_grams',
                ],
            ],
            'recommended_amounts',
        ]);

    expect($configResponse->json('categories.0.key'))->toBeString();
    expect($configResponse->json('zakat.calculator_types.0.key'))->toBeString();
});

test('mobile donation contract exposes zakat calculation payloads', function () {
    $response = $this->postJson('/api/zakat/calculate', [
        'type' => 'fitrah',
        'people_count' => 4,
        'rice_price_per_kg' => 18000,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'type',
            'recommended_amount',
            'is_obligatory',
            'summary',
            'breakdown',
        ]);

    expect($response->json('type'))->toBe('fitrah');
    expect($response->json('recommended_amount'))->toBe(180000);
    expect($response->json('is_obligatory'))->toBeTrue();
});

test('mobile donation contract exposes qris donation creation payloads', function () {
    Storage::fake('local');

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'QRIS Mobile Contract',
        'type' => 'qris',
        'qris_static_payload' => '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18',
        'is_active' => true,
    ]);

    $response = $this->post('/api/donations', [
        'amount' => 75000,
        'category' => 'infak',
        'payment_type' => 'umum',
        'payment_method_id' => $paymentMethod->id,
        'guest_token' => 'guest-mobile-contract',
        'donor_name' => 'Guest Mobile Contract',
        'donor_phone' => '081234567890',
        'donor_email' => 'guest-mobile-contract@example.com',
        'proof_image' => UploadedFile::fake()->image('mobile-proof.jpg'),
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => array_merge(
                mobileDonationContract(),
                ['qris_payload']
            ),
        ]);

    expect($response->json('data.category'))->toBe('infak');
    expect($response->json('data.payment_type'))->toBe('umum');
    expect($response->json('data.payment_method_name'))->toBe('QRIS Mobile Contract');
    expect($response->json('data.qris_payload'))->toBeString()->not->toBe('');
    expect($response->json('data.proof_image_url'))->toBeString();
    expect($response->json('data.is_guest'))->toBeTrue();
});

test('mobile donation contract exposes guest history payloads', function () {
    Storage::fake('local');

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Bank Mobile Contract',
        'is_active' => true,
    ]);

    $donation = Donation::factory()->create([
        'user_id' => null,
        'guest_token' => 'guest-history-contract',
        'donor_name' => 'Guest History Mobile',
        'donor_phone' => '081234567890',
        'donor_email' => 'guest-history@example.com',
        'payment_method_id' => $paymentMethod->id,
        'amount' => 125000,
        'category' => 'sedekah',
        'payment_type' => 'umum',
        'context_slug' => 'sedekah-umum',
        'context_label' => 'Sedekah Umum',
        'proof_image' => 'proofs/mobile-history-proof.jpg',
        'status' => 'verified',
    ]);
    Storage::disk('local')->put($donation->proof_image, 'proof-content');

    $response = $this->getJson('/api/donations/history?guest_token=guest-history-contract');

    assertPaginatedContract($response, mobileDonationContract());
    expect($response->json('data.0.id'))->toBe($donation->uuid);
    expect($response->json('data.0.payment_method_name'))->toBe('Bank Mobile Contract');
    expect($response->json('data.0.proof_image_url'))->toBeString();
    expect($response->json('data.0.is_guest'))->toBeTrue();
});

test('mobile donation contract exposes authenticated history payloads', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile-history-contract')->plainTextToken;
    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Bank Auth Mobile Contract',
        'is_active' => true,
    ]);

    Donation::factory()->create([
        'user_id' => $user->id,
        'guest_token' => null,
        'donor_name' => $user->name,
        'donor_phone' => '081298765432',
        'donor_email' => $user->email,
        'payment_method_id' => $paymentMethod->id,
        'amount' => 250000,
        'category' => 'zakat',
        'payment_type' => 'maal',
        'calculator_type' => 'maal',
        'calculator_breakdown' => [
            'net_assets' => 100000000,
            'nisab_amount' => 85000000,
            'rate' => 0.025,
        ],
        'status' => 'pending',
    ]);

    $response = $this->getJson('/api/donations/history', [
        'Authorization' => 'Bearer '.$token,
    ]);

    assertPaginatedContract($response, mobileDonationContract());
    expect($response->json('data.0.category'))->toBe('zakat');
    expect($response->json('data.0.payment_type'))->toBe('maal');
    expect($response->json('data.0.calculator_type'))->toBe('maal');
    expect($response->json('data.0.is_guest'))->toBeFalse();
});

function assertPaginatedContract(TestResponse $response, array $itemStructure): void
{
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [$itemStructure],
            'meta' => [
                'current_page',
                'last_page',
            ],
        ]);

    expect($response->json('meta.current_page'))->toBeInt();
    expect($response->json('meta.last_page'))->toBeInt();
}

function mobileUserContract(): array
{
    return [
        'id',
        'name',
        'email',
        'phone',
        'avatar_url',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];
}

function mobileArticleContract(): array
{
    return [
        'id',
        'title',
        'slug',
        'content',
        'excerpt',
        'thumbnail',
        'published_at',
    ];
}

function mobileVideoContract(): array
{
    return [
        'id',
        'title',
        'youtube_id',
        'youtube_url',
        'youtube_embed_url',
        'description',
        'thumbnail',
        'published_at',
    ];
}

function mobilePrayerContract(): array
{
    return [
        'id',
        'content',
        'user',
        'user_name',
        'is_anonymous',
        'likes_count',
        'is_liked_by_me',
        'created_at',
    ];
}

function mobilePaymentMethodContract(): array
{
    return [
        'id',
        'name',
        'type',
        'account_number',
        'account_holder',
        'logo_url',
        'qris_image_url',
        'has_qris_template',
    ];
}

function mobileDonationContract(): array
{
    return [
        'id',
        'amount',
        'category',
        'payment_type',
        'context_slug',
        'context_label',
        'intention_note',
        'calculator_type',
        'calculator_breakdown',
        'status',
        'payment_method_id',
        'payment_method_name',
        'proof_image_url',
        'donor_name',
        'donor_phone',
        'donor_email',
        'is_guest',
        'created_at',
    ];
}
