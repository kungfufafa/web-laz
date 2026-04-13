<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TripayClient
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $privateKey = null,
        private readonly ?string $merchantCode = null,
        private readonly ?string $baseUrl = null,
        private readonly ?int $timeout = null,
    ) {
        //
    }

    public function listPaymentChannels(): array
    {
        $response = $this->client()->get('/merchant/payment-channel');

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch Tripay payment channels.');
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    public function createClosedPayment(array $payload): array
    {
        $response = $this->client()->asForm()->post('/transaction/create', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to create Tripay transaction.');
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Tripay create transaction response.');
        }

        return $data;
    }

    public function detailTransaction(string $reference): array
    {
        $response = $this->client()->get('/transaction/detail', [
            'reference' => $reference,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch Tripay transaction detail.');
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Tripay transaction detail response.');
        }

        return $data;
    }

    public function paymentInstructions(string $code, ?string $payCode = null, ?int $amount = null): array
    {
        $response = $this->client()->get('/payment/instruction', array_filter([
            'code' => $code,
            'pay_code' => $payCode,
            'amount' => $amount,
        ], fn (mixed $value): bool => $value !== null && $value !== ''));

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    public function makeTransactionSignature(string $merchantRef, int $amount): string
    {
        return hash_hmac(
            'sha256',
            $this->resolvedMerchantCode().$merchantRef.$amount,
            $this->resolvedPrivateKey(),
        );
    }

    public function verifyCallbackSignature(string $rawBody, ?string $signature): bool
    {
        $privateKey = trim($this->resolvedPrivateKey());

        if (! is_string($signature) || trim($signature) === '') {
            return false;
        }

        if ($privateKey === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $privateKey);

        return hash_equals($expected, trim($signature));
    }

    public function resolvedMerchantCode(): string
    {
        return (string) ($this->merchantCode ?? config('services.tripay.merchant_code'));
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->withToken((string) ($this->apiKey ?? config('services.tripay.api_key')))
            ->baseUrl(rtrim((string) ($this->baseUrl ?? config('services.tripay.base_url')), '/'))
            ->timeout((int) ($this->timeout ?? config('services.tripay.timeout', 30)));
    }

    private function resolvedPrivateKey(): string
    {
        return (string) ($this->privateKey ?? config('services.tripay.private_key'));
    }
}
