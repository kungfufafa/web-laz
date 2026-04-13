<?php

namespace App\Services;

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DigiflazzPpobService
{
    private const STATUS_REFRESH_COOLDOWN_SECONDS = 60;

    private const PREPAID_STATUS_REFRESH_MAX_AGE_DAYS = 90;

    public function __construct(
        private readonly DigiflazzClient $digiflazzClient,
        private readonly PpobPricingService $pricingService,
        private readonly PpobAlertService $alertService,
    ) {}

    public function createInquiry(array $validated, User $user): array
    {
        $product = PpobProduct::query()
            ->where('service_type', PpobTransaction::SERVICE_POSTPAID)
            ->where('buyer_sku_code', $validated['buyer_sku_code'])
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true)
            ->firstOrFail();

        $refId = 'PPOB'.strtoupper(Str::random(18));
        $response = $this->digiflazzClient->inquirePostpaid([
            'buyer_sku_code' => $product->buyer_sku_code,
            'customer_no' => $validated['customer_no'],
            'ref_id' => $refId,
            'extra_fields' => is_array($validated['extra_fields'] ?? null) ? $validated['extra_fields'] : [],
        ]);

        if (strtolower((string) ($response['status'] ?? '')) !== 'sukses') {
            throw ValidationException::withMessages([
                'customer_no' => [(string) ($response['message'] ?? 'Inquiry gagal diproses.')],
            ]);
        }

        $inquiryReference = (string) Str::uuid();
        $expiresAt = now()->endOfDay();
        $pricing = $this->pricingService->resolvePricing([
            'service_type' => PpobTransaction::SERVICE_POSTPAID,
            'category' => $product->category,
            'brand' => $product->brand,
            'buyer_sku_code' => $product->buyer_sku_code,
        ], (float) ($response['selling_price'] ?? $response['price'] ?? 0));
        $payload = [
            'reference' => $inquiryReference,
            'user_id' => $user->id,
            'service_type' => PpobTransaction::SERVICE_POSTPAID,
            'ppob_product_id' => $product->id,
            'ppob_pricing_rule_id' => $pricing['rule']?->id,
            'buyer_sku_code' => $product->buyer_sku_code,
            'product_name' => $product->product_name,
            'category' => $product->category,
            'brand' => $product->brand,
            'type' => $product->type,
            'customer_no' => (string) ($response['customer_no'] ?? $validated['customer_no']),
            'customer_name' => isset($response['customer_name']) ? (string) $response['customer_name'] : null,
            'provider_price' => $pricing['provider_price'],
            'markup_amount' => $pricing['markup_amount'],
            'base_price' => $pricing['sell_price'],
            'provider_payload' => $response,
            'digiflazz_ref_id' => (string) ($response['ref_id'] ?? $refId),
            'expires_at' => $expiresAt->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];

        Cache::put($this->inquiryCacheKey($inquiryReference), $payload, $expiresAt);

        return $payload;
    }

    public function resolveInquiryReference(string $reference, User $user): array
    {
        $payload = Cache::get($this->inquiryCacheKey($reference));

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            throw ValidationException::withMessages([
                'inquiry_reference' => ['Inquiry reference is invalid or has expired.'],
            ]);
        }

        if (blank($payload['expires_at'] ?? null) || now()->greaterThan($payload['expires_at'])) {
            $this->forgetInquiryReference($reference);

            throw ValidationException::withMessages([
                'inquiry_reference' => ['Inquiry reference is no longer valid for payment.'],
            ]);
        }

        return $payload;
    }

    public function forgetInquiryReference(string $reference): void
    {
        Cache::forget($this->inquiryCacheKey($reference));
    }

    public function fulfillTransaction(PpobTransaction $transaction): PpobTransaction
    {
        if ($transaction->payment_status !== PpobTransaction::PAYMENT_PAID) {
            return $transaction;
        }

        if (in_array($transaction->fulfillment_status, [
            PpobTransaction::FULFILLMENT_PROCESSING,
            PpobTransaction::FULFILLMENT_SUCCEEDED,
        ], true)) {
            return $transaction;
        }

        if (
            $transaction->service_type === PpobTransaction::SERVICE_POSTPAID
            && $transaction->inquiry_expires_at !== null
            && now()->greaterThan($transaction->inquiry_expires_at)
        ) {
            $transaction->forceFill([
                'fulfillment_status' => PpobTransaction::FULFILLMENT_MANUAL_REVIEW,
                'fulfillment_message' => 'Pembayaran sudah diterima, tetapi inquiry Digiflazz sudah kedaluwarsa. Transaksi perlu ditindaklanjuti manual.',
            ])->save();

            $this->alertService->logError('Paid PPOB postpaid transaction requires manual review because Digiflazz inquiry expired.', [
                'transaction_uuid' => $transaction->uuid,
                'digiflazz_ref_id' => $transaction->digiflazz_ref_id,
                'inquiry_expires_at' => $transaction->inquiry_expires_at?->toIso8601String(),
            ]);

            return $transaction->fresh() ?? $transaction;
        }

        $transaction->forceFill([
            'fulfillment_status' => PpobTransaction::FULFILLMENT_PROCESSING,
            'fulfillment_message' => 'Sedang memproses transaksi ke Digiflazz.',
        ])->save();

        $payload = $transaction->service_type === PpobTransaction::SERVICE_PREPAID
            ? $this->digiflazzClient->topup([
                'buyer_sku_code' => $transaction->buyer_sku_code,
                'customer_no' => $transaction->customer_no,
                'ref_id' => $transaction->digiflazz_ref_id,
            ])
            : $this->digiflazzClient->payPostpaid([
                'buyer_sku_code' => $transaction->buyer_sku_code,
                'customer_no' => $transaction->customer_no,
                'ref_id' => $transaction->digiflazz_ref_id,
            ]);

        $this->applyPayload($transaction, $payload);

        return $transaction->fresh() ?? $transaction;
    }

    public function refreshTransactionStatus(PpobTransaction $transaction): PpobTransaction
    {
        if (! $this->canRefreshTransactionStatus($transaction)) {
            return $transaction->fresh() ?? $transaction;
        }

        if ($this->shouldSkipPrepaidStatusRefreshDueToAge($transaction)) {
            $this->alertService->logWarning('Skipping Digiflazz prepaid status refresh after safe age window.', [
                'transaction_uuid' => $transaction->uuid,
                'digiflazz_ref_id' => $transaction->digiflazz_ref_id,
                'created_at' => $transaction->created_at?->toIso8601String(),
            ]);

            return $transaction->fresh() ?? $transaction;
        }

        $payload = $transaction->service_type === PpobTransaction::SERVICE_PREPAID
            ? $this->digiflazzClient->checkPrepaidStatus(
                $transaction->buyer_sku_code,
                $transaction->customer_no,
                $transaction->digiflazz_ref_id,
            )
            : $this->digiflazzClient->checkPostpaidStatus(
                $transaction->buyer_sku_code,
                $transaction->customer_no,
                $transaction->digiflazz_ref_id,
            );

        $this->applyPayload($transaction, $payload);

        return $transaction->fresh() ?? $transaction;
    }

    public function applyPayload(PpobTransaction $transaction, array $payload): void
    {
        $status = strtolower((string) ($payload['status'] ?? ''));

        $transaction->forceFill([
            'customer_name' => isset($payload['customer_name']) && $payload['customer_name'] !== ''
                ? (string) $payload['customer_name']
                : $transaction->customer_name,
            'digiflazz_status' => isset($payload['status']) ? (string) $payload['status'] : null,
            'digiflazz_rc' => isset($payload['rc']) ? (string) $payload['rc'] : null,
            'digiflazz_sn' => isset($payload['sn']) ? (string) $payload['sn'] : null,
            'digiflazz_payload' => $payload,
            'fulfillment_status' => match ($status) {
                'sukses' => PpobTransaction::FULFILLMENT_SUCCEEDED,
                'gagal' => PpobTransaction::FULFILLMENT_FAILED,
                default => PpobTransaction::FULFILLMENT_PROCESSING,
            },
            'fulfillment_message' => isset($payload['message']) ? (string) $payload['message'] : $transaction->fulfillment_message,
            'provider_price' => isset($payload['selling_price'])
                ? (float) $payload['selling_price']
                : (isset($payload['price']) ? (float) $payload['price'] : $transaction->provider_price),
        ])->save();

        if ($transaction->fulfillment_status === PpobTransaction::FULFILLMENT_FAILED) {
            $this->alertService->logError('PPOB fulfillment failed.', [
                'transaction_uuid' => $transaction->uuid,
                'digiflazz_ref_id' => $transaction->digiflazz_ref_id,
                'message' => $transaction->fulfillment_message,
                'status' => $transaction->digiflazz_status,
                'rc' => $transaction->digiflazz_rc,
            ]);
        }
    }

    private function canRefreshTransactionStatus(PpobTransaction $transaction): bool
    {
        if ($transaction->payment_status !== PpobTransaction::PAYMENT_PAID) {
            return false;
        }

        if ($transaction->fulfillment_status !== PpobTransaction::FULFILLMENT_PROCESSING) {
            return false;
        }

        if ($transaction->updated_at === null) {
            return true;
        }

        return $transaction->updated_at->lte(now()->subSeconds(self::STATUS_REFRESH_COOLDOWN_SECONDS));
    }

    private function shouldSkipPrepaidStatusRefreshDueToAge(PpobTransaction $transaction): bool
    {
        if ($transaction->service_type !== PpobTransaction::SERVICE_PREPAID) {
            return false;
        }

        if ($transaction->created_at === null) {
            return false;
        }

        return $transaction->created_at->lte(now()->subDays(self::PREPAID_STATUS_REFRESH_MAX_AGE_DAYS));
    }

    private function inquiryCacheKey(string $reference): string
    {
        return 'ppob:inquiry:'.$reference;
    }
}
