<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DigiflazzClient
{
    public function __construct(
        private readonly ?string $username = null,
        private readonly ?string $apiKey = null,
        private readonly ?string $baseUrl = null,
        private readonly ?string $webhookSecret = null,
        private readonly ?bool $testing = null,
        private readonly ?int $timeout = null,
    ) {
        //
    }

    public function fetchPriceList(string $command): array
    {
        if (! in_array($command, ['prepaid', 'pasca'], true)) {
            throw new RuntimeException('Unsupported Digiflazz price list command.');
        }

        $response = $this->client()->post('/price-list', [
            'cmd' => $command,
            'username' => $this->resolvedUsername(),
            'sign' => md5($this->resolvedUsername().$this->resolvedApiKey().'pricelist'),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch Digiflazz price list.');
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    public function topup(array $payload): array
    {
        return $this->postTransactionPayload(array_filter([
            'username' => $this->resolvedUsername(),
            'buyer_sku_code' => $payload['buyer_sku_code'] ?? null,
            'customer_no' => $payload['customer_no'] ?? null,
            'ref_id' => $payload['ref_id'] ?? null,
            'testing' => $payload['testing'] ?? $this->resolvedTesting(),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function inquirePostpaid(array $payload): array
    {
        return $this->postTransactionPayload(array_filter([
            'commands' => 'inq-pasca',
            'username' => $this->resolvedUsername(),
            'buyer_sku_code' => $payload['buyer_sku_code'] ?? null,
            'customer_no' => $payload['customer_no'] ?? null,
            'ref_id' => $payload['ref_id'] ?? null,
            'testing' => $payload['testing'] ?? $this->resolvedTesting(),
            ...((is_array($payload['extra_fields'] ?? null) ? $payload['extra_fields'] : [])),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function payPostpaid(array $payload): array
    {
        return $this->postTransactionPayload(array_filter([
            'commands' => 'pay-pasca',
            'username' => $this->resolvedUsername(),
            'buyer_sku_code' => $payload['buyer_sku_code'] ?? null,
            'customer_no' => $payload['customer_no'] ?? null,
            'ref_id' => $payload['ref_id'] ?? null,
            ...((is_array($payload['extra_fields'] ?? null) ? $payload['extra_fields'] : [])),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function checkPrepaidStatus(string $buyerSkuCode, string $customerNo, string $refId): array
    {
        return $this->postTransactionPayload([
            'username' => $this->resolvedUsername(),
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
        ]);
    }

    public function checkPostpaidStatus(string $buyerSkuCode, string $customerNo, string $refId): array
    {
        return $this->postTransactionPayload([
            'commands' => 'status-pasca',
            'username' => $this->resolvedUsername(),
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
        ]);
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) ($this->webhookSecret ?? config('services.digiflazz.webhook_secret'));

        if ($secret === '') {
            return false;
        }

        if (! is_string($signature) || trim($signature) === '') {
            return false;
        }

        $expected = 'sha1='.hash_hmac('sha1', $rawBody, $secret);

        return hash_equals($expected, trim($signature));
    }

    private function postTransactionPayload(array $payload): array
    {
        $refId = (string) ($payload['ref_id'] ?? '');
        $payload['sign'] = md5($this->resolvedUsername().$this->resolvedApiKey().$refId);

        $response = $this->client()->post('/transaction', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to communicate with Digiflazz transaction endpoint.');
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Digiflazz transaction response.');
        }

        return $data;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->baseUrl(rtrim((string) ($this->baseUrl ?? config('services.digiflazz.base_url')), '/'))
            ->timeout((int) ($this->timeout ?? config('services.digiflazz.timeout', 30)));
    }

    private function resolvedUsername(): string
    {
        return (string) ($this->username ?? config('services.digiflazz.username'));
    }

    private function resolvedApiKey(): string
    {
        return (string) ($this->apiKey ?? config('services.digiflazz.api_key'));
    }

    private function resolvedTesting(): bool
    {
        return (bool) ($this->testing ?? config('services.digiflazz.testing', false));
    }
}
