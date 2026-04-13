<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransClient
{
    private const CHANNEL_DEFINITIONS = [
        'bri_va' => ['group' => 'Virtual Account', 'code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'type' => 'redirect'],
        'bni_va' => ['group' => 'Virtual Account', 'code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'type' => 'redirect'],
        'bca_va' => ['group' => 'Virtual Account', 'code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'type' => 'redirect'],
        'permata_va' => ['group' => 'Virtual Account', 'code' => 'PERMATA', 'name' => 'Permata Virtual Account', 'type' => 'redirect'],
        'echannel' => ['group' => 'Virtual Account', 'code' => 'MANDIRI', 'name' => 'Mandiri Bill', 'type' => 'redirect'],
        'qris' => ['group' => 'QRIS', 'code' => 'QRIS', 'name' => 'QRIS', 'type' => 'redirect'],
        'gopay' => ['group' => 'E-Wallet', 'code' => 'GOPAY', 'name' => 'GoPay', 'type' => 'redirect'],
        'shopeepay' => ['group' => 'E-Wallet', 'code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'type' => 'redirect'],
    ];

    private const CHANNEL_ALIASES = [
        'bank_transfer' => ['permata_va', 'bca_va', 'bni_va', 'bri_va', 'echannel'],
    ];

    public function __construct(
        private readonly ?string $serverKey = null,
        private readonly ?string $clientKey = null,
        private readonly ?string $merchantId = null,
        private readonly ?bool $isProduction = null,
        private readonly ?string $snapBaseUrl = null,
        private readonly ?string $apiBaseUrl = null,
        private readonly array|string|null $enabledPayments = null,
        private readonly ?int $timeout = null,
    ) {
        //
    }

    public function listPaymentChannels(): array
    {
        $channels = [];

        foreach ($this->resolvedEnabledPayments() as $paymentCode) {
            foreach ($this->expandPaymentCode($paymentCode) as $expandedCode) {
                $definition = self::CHANNEL_DEFINITIONS[$expandedCode] ?? null;

                if (! is_array($definition)) {
                    continue;
                }

                $channels[$definition['code']] = [
                    ...$definition,
                    'active' => true,
                    'midtrans_code' => $expandedCode,
                ];
            }
        }

        return array_values($channels);
    }

    public function resolvePaymentChannel(string $requestedCode): ?array
    {
        $normalizedCode = strtoupper(trim($requestedCode));
        $normalizedMidtransCode = strtolower(trim($requestedCode));

        foreach ($this->listPaymentChannels() as $channel) {
            if (strtoupper((string) $channel['code']) === $normalizedCode) {
                return $channel;
            }

            if (strtolower((string) $channel['midtrans_code']) === $normalizedMidtransCode) {
                return $channel;
            }
        }

        return null;
    }

    public function resolveChannelFromPayload(array $payload, ?string $fallbackCode = null): ?array
    {
        $paymentType = strtolower((string) ($payload['payment_type'] ?? ''));

        if ($paymentType === 'bank_transfer') {
            $bank = strtolower((string) data_get($payload, 'va_numbers.0.bank'));

            if ($bank !== '') {
                return $this->resolvePaymentChannel($bank.'_va');
            }

            if (filled($payload['permata_va_number'] ?? null)) {
                return $this->resolvePaymentChannel('permata_va');
            }
        }

        if ($paymentType !== '') {
            $channel = $this->resolvePaymentChannel($paymentType);

            if (is_array($channel)) {
                return $channel;
            }
        }

        if (is_string($fallbackCode) && trim($fallbackCode) !== '') {
            return $this->resolvePaymentChannel($fallbackCode);
        }

        return null;
    }

    public function createSnapTransaction(array $payload): array
    {
        $response = $this->snapClient()->post('/snap/v1/transactions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to create Midtrans Snap transaction.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Midtrans Snap response.');
        }

        return $data;
    }

    public function getTransactionStatus(string $orderId): array
    {
        $response = $this->apiClient()->get('/v2/'.rawurlencode($orderId).'/status');

        if ($response->status() === 404) {
            return [];
        }

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch Midtrans transaction status.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Midtrans status response.');
        }

        return $data;
    }

    public function verifyNotificationSignature(array $payload): bool
    {
        $signature = trim((string) ($payload['signature_key'] ?? ''));
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $serverKey = trim($this->resolvedServerKey());

        if ($signature === '' || $orderId === '' || $statusCode === '' || $grossAmount === '' || $serverKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    public function resolvedClientKey(): string
    {
        return (string) ($this->clientKey ?? config('services.midtrans.client_key'));
    }

    public function resolvedMerchantId(): string
    {
        return (string) ($this->merchantId ?? config('services.midtrans.merchant_id'));
    }

    private function resolvedEnabledPayments(): array
    {
        $configured = $this->enabledPayments ?? config('services.midtrans.enabled_payments', []);

        if (is_string($configured)) {
            $configured = explode(',', $configured);
        }

        if (! is_array($configured)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $payment): string => trim((string) $payment),
            $configured,
        )));
    }

    private function expandPaymentCode(string $paymentCode): array
    {
        $normalized = strtolower(trim($paymentCode));

        if (isset(self::CHANNEL_DEFINITIONS[$normalized])) {
            return [$normalized];
        }

        return self::CHANNEL_ALIASES[$normalized] ?? [];
    }

    private function snapClient(): PendingRequest
    {
        return Http::acceptJson()
            ->withBasicAuth($this->resolvedServerKey(), '')
            ->baseUrl(rtrim($this->resolvedSnapBaseUrl(), '/'))
            ->timeout((int) ($this->timeout ?? config('services.midtrans.timeout', 30)));
    }

    private function apiClient(): PendingRequest
    {
        return Http::acceptJson()
            ->withBasicAuth($this->resolvedServerKey(), '')
            ->baseUrl(rtrim($this->resolvedApiBaseUrl(), '/'))
            ->timeout((int) ($this->timeout ?? config('services.midtrans.timeout', 30)));
    }

    private function resolvedServerKey(): string
    {
        return (string) ($this->serverKey ?? config('services.midtrans.server_key'));
    }

    private function resolvedSnapBaseUrl(): string
    {
        if (filled($this->snapBaseUrl)) {
            return (string) $this->snapBaseUrl;
        }

        if (filled(config('services.midtrans.snap_base_url'))) {
            return (string) config('services.midtrans.snap_base_url');
        }

        return $this->isProductionEnabled()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    private function resolvedApiBaseUrl(): string
    {
        if (filled($this->apiBaseUrl)) {
            return (string) $this->apiBaseUrl;
        }

        if (filled(config('services.midtrans.api_base_url'))) {
            return (string) config('services.midtrans.api_base_url');
        }

        return $this->isProductionEnabled()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function isProductionEnabled(): bool
    {
        return (bool) ($this->isProduction ?? config('services.midtrans.is_production', false));
    }
}
