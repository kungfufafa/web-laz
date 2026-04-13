<?php

namespace App\Services;

use App\Jobs\ProcessPpobFulfillment;
use App\Jobs\ReconcilePpobTransaction;
use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PpobTransactionService
{
    public function __construct(
        private readonly TripayClient $tripayClient,
        private readonly MidtransClient $midtransClient,
        private readonly DigiflazzPpobService $digiflazzPpobService,
        private readonly PpobAlertService $alertService,
    ) {}

    public function paymentChannels(): array
    {
        return match ($this->paymentGateway()) {
            'midtrans' => $this->midtransClient->listPaymentChannels(),
            'tripay' => array_values(array_filter(
                $this->tripayClient->listPaymentChannels(),
                fn (mixed $channel): bool => is_array($channel) && (bool) ($channel['active'] ?? false),
            )),
            default => [],
        };
    }

    public function createInquiry(array $validated, User $user): array
    {
        return $this->digiflazzPpobService->createInquiry($validated, $user);
    }

    public function createTransaction(array $validated, User $user): PpobTransaction
    {
        $serviceType = $validated['service_type'];
        $channel = $this->resolvePaymentChannel($validated['payment_channel_code']);

        if ($serviceType === PpobTransaction::SERVICE_POSTPAID) {
            $inquiry = $this->digiflazzPpobService->resolveInquiryReference($validated['inquiry_reference'], $user);

            $transaction = $this->createGatewayTransaction(
                user: $user,
                serviceType: $serviceType,
                channel: $channel,
                buyerSkuCode: (string) $inquiry['buyer_sku_code'],
                productName: (string) $inquiry['product_name'],
                category: $inquiry['category'] ?? null,
                brand: $inquiry['brand'] ?? null,
                type: $inquiry['type'] ?? null,
                customerNo: (string) $inquiry['customer_no'],
                customerName: $inquiry['customer_name'] ?? null,
                providerPrice: (float) ($inquiry['provider_price'] ?? $inquiry['base_price']),
                sellPrice: (float) $inquiry['base_price'],
                markupAmount: (float) ($inquiry['markup_amount'] ?? 0),
                ppobProductId: isset($inquiry['ppob_product_id']) ? (int) $inquiry['ppob_product_id'] : null,
                ppobPricingRuleId: isset($inquiry['ppob_pricing_rule_id']) ? (int) $inquiry['ppob_pricing_rule_id'] : null,
                digiflazzRefId: (string) $inquiry['digiflazz_ref_id'],
                inquiryReference: (string) $inquiry['reference'],
                inquiryPayload: $inquiry['provider_payload'] ?? null,
                inquiryExpiresAt: $inquiry['expires_at'] ?? null,
            );

            $this->digiflazzPpobService->forgetInquiryReference($validated['inquiry_reference']);

            return $transaction;
        }

        $product = PpobProduct::query()
            ->where('service_type', PpobTransaction::SERVICE_PREPAID)
            ->where('buyer_sku_code', $validated['buyer_sku_code'])
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true)
            ->firstOrFail();

        return $this->createGatewayTransaction(
            user: $user,
            serviceType: $serviceType,
            channel: $channel,
            buyerSkuCode: $product->buyer_sku_code,
            productName: $product->product_name,
            category: $product->category,
            brand: $product->brand,
            type: $product->type,
            customerNo: $validated['customer_no'],
            customerName: null,
            providerPrice: (float) ($product->resolvedProviderPrice() ?? 0),
            sellPrice: (float) ($product->resolvedSellPrice() ?? 0),
            markupAmount: (float) ($product->markup_amount ?? 0),
            ppobProductId: $product->id,
            ppobPricingRuleId: $product->ppob_pricing_rule_id,
            digiflazzRefId: 'PPOB'.strtoupper(Str::random(18)),
        );
    }

    public function history(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return PpobTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function fulfillTransaction(string $uuid): PpobTransaction
    {
        return DB::transaction(function () use ($uuid): PpobTransaction {
            /** @var PpobTransaction $transaction */
            $transaction = PpobTransaction::query()
                ->where('uuid', $uuid)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->digiflazzPpobService->fulfillTransaction($transaction);
        });
    }

    public function refreshTransactionStatus(string $uuid): PpobTransaction
    {
        return DB::transaction(function () use ($uuid): PpobTransaction {
            /** @var PpobTransaction $transaction */
            $transaction = PpobTransaction::query()
                ->where('uuid', $uuid)
                ->lockForUpdate()
                ->firstOrFail();

            $paymentGateway = $transaction->resolvedPaymentGateway();

            if ($paymentGateway === 'midtrans' && filled($transaction->midtrans_order_id)) {
                $midtransPayload = $this->midtransClient->getTransactionStatus($transaction->midtrans_order_id);

                if ($midtransPayload !== []) {
                    $this->syncMidtransPayload($transaction, $midtransPayload);
                }
            } elseif ($paymentGateway === 'tripay' && filled($transaction->payment_gateway_reference)) {
                $tripayPayload = $this->tripayClient->detailTransaction($transaction->payment_gateway_reference);
                $this->syncTripayPayload($transaction, $tripayPayload);
            }

            if ($transaction->shouldDispatchFulfillment()) {
                $this->dispatchFulfillment($transaction->uuid);

                return $transaction->fresh() ?? $transaction;
            }

            if ($transaction->payment_status === PpobTransaction::PAYMENT_PAID) {
                return $this->digiflazzPpobService->refreshTransactionStatus($transaction);
            }

            return $transaction->fresh() ?? $transaction;
        });
    }

    public function reconcileTransactions(int $limit = 50): int
    {
        $count = 0;
        $successfulPaymentCutoff = now()->subHours($this->reconcileSuccessfulPaymentWindowHours());

        PpobTransaction::query()
            ->where(function ($query) use ($successfulPaymentCutoff): void {
                $query
                    ->where('payment_status', PpobTransaction::PAYMENT_UNPAID)
                    ->orWhereIn('fulfillment_status', [
                        PpobTransaction::FULFILLMENT_PENDING,
                        PpobTransaction::FULFILLMENT_PROCESSING,
                        PpobTransaction::FULFILLMENT_FAILED,
                    ])
                    ->orWhere(function ($successfulQuery) use ($successfulPaymentCutoff): void {
                        $successfulQuery
                            ->where('payment_status', PpobTransaction::PAYMENT_PAID)
                            ->where('fulfillment_status', PpobTransaction::FULFILLMENT_SUCCEEDED)
                            ->whereNotNull('paid_at')
                            ->where('paid_at', '>=', $successfulPaymentCutoff)
                            ->where(function ($gatewayQuery): void {
                                $gatewayQuery
                                    ->whereNotNull('midtrans_order_id')
                                    ->orWhereNotNull('payment_gateway_reference');
                            });
                    });
            })
            ->orderByDesc('updated_at')
            ->limit(max(1, $limit))
            ->pluck('uuid')
            ->each(function (string $uuid) use (&$count): void {
                $this->dispatchReconciliation($uuid);
                $count++;
            });

        return $count;
    }

    private function createGatewayTransaction(
        User $user,
        string $serviceType,
        array $channel,
        string $buyerSkuCode,
        string $productName,
        ?string $category,
        ?string $brand,
        ?string $type,
        string $customerNo,
        ?string $customerName,
        float $providerPrice,
        float $sellPrice,
        float $markupAmount,
        ?int $ppobProductId = null,
        ?int $ppobPricingRuleId = null,
        ?string $digiflazzRefId = null,
        ?string $inquiryReference = null,
        mixed $inquiryPayload = null,
        ?string $inquiryExpiresAt = null,
    ): PpobTransaction {
        return match ($this->paymentGateway()) {
            'midtrans' => $this->createMidtransTransaction(
                user: $user,
                serviceType: $serviceType,
                channel: $channel,
                buyerSkuCode: $buyerSkuCode,
                productName: $productName,
                category: $category,
                brand: $brand,
                type: $type,
                customerNo: $customerNo,
                customerName: $customerName,
                providerPrice: $providerPrice,
                sellPrice: $sellPrice,
                markupAmount: $markupAmount,
                ppobProductId: $ppobProductId,
                ppobPricingRuleId: $ppobPricingRuleId,
                digiflazzRefId: $digiflazzRefId,
                inquiryReference: $inquiryReference,
                inquiryPayload: $inquiryPayload,
                inquiryExpiresAt: $inquiryExpiresAt,
            ),
            'tripay' => $this->createTripayTransaction(
                user: $user,
                serviceType: $serviceType,
                channel: $channel,
                buyerSkuCode: $buyerSkuCode,
                productName: $productName,
                category: $category,
                brand: $brand,
                type: $type,
                customerNo: $customerNo,
                customerName: $customerName,
                providerPrice: $providerPrice,
                sellPrice: $sellPrice,
                markupAmount: $markupAmount,
                ppobProductId: $ppobProductId,
                ppobPricingRuleId: $ppobPricingRuleId,
                digiflazzRefId: $digiflazzRefId,
                inquiryReference: $inquiryReference,
                inquiryPayload: $inquiryPayload,
                inquiryExpiresAt: $inquiryExpiresAt,
            ),
            default => throw new HttpException(503, 'PPOB checkout sementara tidak tersedia karena gateway pembayaran belum dikonfigurasi.'),
        };
    }

    private function createMidtransTransaction(
        User $user,
        string $serviceType,
        array $channel,
        string $buyerSkuCode,
        string $productName,
        ?string $category,
        ?string $brand,
        ?string $type,
        string $customerNo,
        ?string $customerName,
        float $providerPrice,
        float $sellPrice,
        float $markupAmount,
        ?int $ppobProductId = null,
        ?int $ppobPricingRuleId = null,
        ?string $digiflazzRefId = null,
        ?string $inquiryReference = null,
        mixed $inquiryPayload = null,
        ?string $inquiryExpiresAt = null,
    ): PpobTransaction {
        $merchantRef = 'PPOB-'.strtoupper(Str::random(18));
        $baseAmount = (int) round($sellPrice);
        $createdAt = now()->timezone('Asia/Jakarta');
        $expiresAt = $createdAt->copy()->addMinutes($this->midtransExpiryMinutes());

        if ($baseAmount <= 0) {
            throw ValidationException::withMessages([
                'buyer_sku_code' => ['Product price is not available for checkout.'],
            ]);
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $merchantRef,
                'gross_amount' => $baseAmount,
            ],
            'item_details' => [[
                'id' => $buyerSkuCode,
                'price' => $baseAmount,
                'quantity' => 1,
                'name' => Str::limit($productName, 50, ''),
                'brand' => $brand,
                'category' => $category,
                'merchant_name' => config('app.name'),
            ]],
            'customer_details' => array_filter([
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ], fn (mixed $value): bool => filled($value)),
            'enabled_payments' => [(string) $channel['midtrans_code']],
            'credit_card' => [
                'secure' => true,
            ],
            'expiry' => [
                'start_time' => $createdAt->format('Y-m-d H:i:s O'),
                'duration' => $this->midtransExpiryMinutes(),
                'unit' => 'minute',
            ],
            'page_expiry' => [
                'duration' => $this->midtransExpiryMinutes(),
                'unit' => 'minute',
            ],
        ];

        if (filled(config('services.midtrans.finish_url'))) {
            $payload['callbacks'] = [
                'finish' => (string) config('services.midtrans.finish_url'),
            ];
        }

        $midtransResponse = $this->midtransClient->createSnapTransaction($payload);

        return PpobTransaction::query()->create([
            'user_id' => $user->id,
            'ppob_product_id' => $ppobProductId,
            'ppob_pricing_rule_id' => $ppobPricingRuleId,
            'provider' => 'digiflazz',
            'service_type' => $serviceType,
            'buyer_sku_code' => $buyerSkuCode,
            'product_name' => $productName,
            'category' => $category,
            'brand' => $brand,
            'type' => $type,
            'customer_no' => $customerNo,
            'customer_name' => $customerName,
            'inquiry_reference' => $inquiryReference,
            'inquiry_payload' => is_array($inquiryPayload) ? $inquiryPayload : null,
            'inquiry_expires_at' => $inquiryExpiresAt,
            'provider_price' => $providerPrice,
            'markup_amount' => $markupAmount,
            'base_price' => $baseAmount,
            'fee_customer' => 0,
            'fee_merchant' => 0,
            'total_amount' => $baseAmount,
            'amount_received' => 0,
            'payment_channel_code' => (string) $channel['code'],
            'payment_channel_name' => (string) ($channel['name'] ?? $channel['code']),
            'payment_status' => PpobTransaction::PAYMENT_UNPAID,
            'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
            'midtrans_order_id' => $merchantRef,
            'midtrans_transaction_id' => isset($midtransResponse['transaction_id']) ? (string) $midtransResponse['transaction_id'] : null,
            'midtrans_snap_token' => isset($midtransResponse['token']) ? (string) $midtransResponse['token'] : null,
            'midtrans_redirect_url' => isset($midtransResponse['redirect_url']) ? (string) $midtransResponse['redirect_url'] : null,
            'midtrans_payment_type' => (string) ($channel['midtrans_code'] ?? ''),
            'midtrans_expired_at' => $expiresAt,
            'midtrans_payload' => $midtransResponse,
            'payment_gateway_order_id' => $merchantRef,
            'digiflazz_ref_id' => (string) ($digiflazzRefId ?? 'PPOB'.strtoupper(Str::random(18))),
            'metadata' => [
                'payment_gateway' => 'midtrans',
                'payment_instructions' => [],
            ],
        ]);
    }

    private function createTripayTransaction(
        User $user,
        string $serviceType,
        array $channel,
        string $buyerSkuCode,
        string $productName,
        ?string $category,
        ?string $brand,
        ?string $type,
        string $customerNo,
        ?string $customerName,
        float $providerPrice,
        float $sellPrice,
        float $markupAmount,
        ?int $ppobProductId = null,
        ?int $ppobPricingRuleId = null,
        ?string $digiflazzRefId = null,
        ?string $inquiryReference = null,
        mixed $inquiryPayload = null,
        ?string $inquiryExpiresAt = null,
    ): PpobTransaction {
        $merchantRef = 'PPOB-'.strtoupper(Str::random(18));
        $baseAmount = (int) round($sellPrice);

        if ($baseAmount <= 0) {
            throw ValidationException::withMessages([
                'buyer_sku_code' => ['Product price is not available for checkout.'],
            ]);
        }

        $tripayPayload = [
            'method' => (string) $channel['code'],
            'merchant_ref' => $merchantRef,
            'amount' => $baseAmount,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'order_items' => [
                [
                    'sku' => $buyerSkuCode,
                    'name' => $productName,
                    'price' => $baseAmount,
                    'quantity' => 1,
                ],
            ],
            'expired_time' => now()->addMinutes((int) config('services.tripay.expiry_minutes', 60))->timestamp,
            'callback_url' => route('api.ppob.callbacks.tripay'),
            'signature' => $this->tripayClient->makeTransactionSignature(
                $merchantRef,
                $baseAmount,
            ),
        ];

        if (filled(config('services.tripay.return_url'))) {
            $tripayPayload['return_url'] = config('services.tripay.return_url');
        }

        $tripayResponse = $this->tripayClient->createClosedPayment($tripayPayload);
        $instructions = $this->tripayClient->paymentInstructions(
            (string) ($tripayResponse['payment_method'] ?? $channel['code']),
            isset($tripayResponse['pay_code']) ? (string) $tripayResponse['pay_code'] : null,
            isset($tripayResponse['amount']) ? (int) $tripayResponse['amount'] : $baseAmount,
        );

        $transaction = PpobTransaction::query()->create([
            'user_id' => $user->id,
            'ppob_product_id' => $ppobProductId,
            'ppob_pricing_rule_id' => $ppobPricingRuleId,
            'provider' => 'digiflazz',
            'service_type' => $serviceType,
            'buyer_sku_code' => $buyerSkuCode,
            'product_name' => $productName,
            'category' => $category,
            'brand' => $brand,
            'type' => $type,
            'customer_no' => $customerNo,
            'customer_name' => $customerName,
            'inquiry_reference' => $inquiryReference,
            'inquiry_payload' => is_array($inquiryPayload) ? $inquiryPayload : null,
            'inquiry_expires_at' => $inquiryExpiresAt,
            'provider_price' => $providerPrice,
            'markup_amount' => $markupAmount,
            'base_price' => $baseAmount,
            'fee_customer' => (float) ($tripayResponse['fee_customer'] ?? 0),
            'fee_merchant' => (float) ($tripayResponse['fee_merchant'] ?? 0),
            'total_amount' => (float) ($tripayResponse['amount'] ?? $baseAmount),
            'amount_received' => (float) ($tripayResponse['amount_received'] ?? 0),
            'payment_channel_code' => (string) ($tripayResponse['payment_method'] ?? $channel['code']),
            'payment_channel_name' => (string) ($tripayResponse['payment_name'] ?? $channel['name'] ?? $channel['code']),
            'payment_status' => PpobTransaction::PAYMENT_UNPAID,
            'fulfillment_status' => PpobTransaction::FULFILLMENT_PENDING,
            'payment_gateway_reference' => isset($tripayResponse['reference']) ? (string) $tripayResponse['reference'] : null,
            'payment_gateway_order_id' => $merchantRef,
            'payment_gateway_checkout_url' => isset($tripayResponse['checkout_url']) ? (string) $tripayResponse['checkout_url'] : null,
            'payment_gateway_pay_url' => isset($tripayResponse['pay_url']) ? (string) $tripayResponse['pay_url'] : null,
            'payment_gateway_pay_code' => isset($tripayResponse['pay_code']) ? (string) $tripayResponse['pay_code'] : null,
            'payment_gateway_expired_at' => isset($tripayResponse['expired_time'])
                ? now()->createFromTimestamp((int) $tripayResponse['expired_time'])
                : null,
            'payment_gateway_payload' => $tripayResponse,
            'digiflazz_ref_id' => (string) ($digiflazzRefId ?? 'PPOB'.strtoupper(Str::random(18))),
            'metadata' => [
                'payment_gateway' => 'tripay',
                'payment_instructions' => $instructions,
            ],
        ]);

        if (($tripayResponse['status'] ?? null) === 'PAID') {
            $transaction->forceFill([
                'payment_status' => PpobTransaction::PAYMENT_PAID,
                'paid_at' => isset($tripayResponse['paid_at'])
                    ? now()->createFromTimestamp((int) $tripayResponse['paid_at'])
                    : now(),
            ])->save();

            $this->dispatchFulfillment($transaction->uuid);
        }

        return $transaction;
    }

    private function resolvePaymentChannel(string $method): array
    {
        if ($this->paymentGateway() === 'midtrans') {
            $channel = $this->midtransClient->resolvePaymentChannel($method);

            if (is_array($channel)) {
                return $channel;
            }
        }

        foreach ($this->paymentChannels() as $channel) {
            if (is_array($channel) && (string) ($channel['code'] ?? '') === $method) {
                return $channel;
            }
        }

        throw ValidationException::withMessages([
            'payment_channel_code' => ['Selected payment channel is not available.'],
        ]);
    }

    public function syncTripayPayload(PpobTransaction $transaction, array $payload): void
    {
        $paymentStatus = strtoupper((string) ($payload['status'] ?? 'UNPAID'));

        $transaction->forceFill([
            'payment_gateway_reference' => isset($payload['reference'])
                ? (string) $payload['reference']
                : $transaction->payment_gateway_reference,
            'payment_gateway_order_id' => isset($payload['merchant_ref'])
                ? (string) $payload['merchant_ref']
                : $transaction->payment_gateway_order_id,
            'payment_gateway_checkout_url' => isset($payload['checkout_url'])
                ? (string) $payload['checkout_url']
                : $transaction->payment_gateway_checkout_url,
            'payment_gateway_pay_url' => isset($payload['pay_url'])
                ? (string) $payload['pay_url']
                : $transaction->payment_gateway_pay_url,
            'payment_gateway_pay_code' => isset($payload['pay_code'])
                ? (string) $payload['pay_code']
                : $transaction->payment_gateway_pay_code,
            'payment_gateway_expired_at' => isset($payload['expired_time'])
                ? now()->createFromTimestamp((int) $payload['expired_time'])
                : $transaction->payment_gateway_expired_at,
            'payment_channel_name' => (string) ($payload['payment_method'] ?? $transaction->payment_channel_name),
            'payment_channel_code' => (string) ($payload['payment_method_code'] ?? $transaction->payment_channel_code),
            'total_amount' => (float) ($payload['total_amount'] ?? $payload['amount'] ?? $transaction->total_amount),
            'fee_merchant' => (float) ($payload['fee_merchant'] ?? $transaction->fee_merchant),
            'fee_customer' => (float) ($payload['fee_customer'] ?? $transaction->fee_customer),
            'amount_received' => (float) ($payload['amount_received'] ?? $transaction->amount_received),
            'payment_gateway_payload' => $payload,
            'payment_status' => match ($paymentStatus) {
                'PAID' => PpobTransaction::PAYMENT_PAID,
                'EXPIRED' => PpobTransaction::PAYMENT_EXPIRED,
                'FAILED' => PpobTransaction::PAYMENT_FAILED,
                default => PpobTransaction::PAYMENT_UNPAID,
            },
            'paid_at' => $paymentStatus === 'PAID' && isset($payload['paid_at'])
                ? now()->createFromTimestamp((int) $payload['paid_at'])
                : $transaction->paid_at,
            'expired_at' => $paymentStatus === 'EXPIRED'
                ? now()
                : $transaction->expired_at,
            'metadata' => $this->mergedMetadata($transaction, [
                'payment_gateway' => 'tripay',
                'payment_instructions' => data_get($transaction->metadata, 'payment_instructions', []),
            ]),
        ])->save();

        if (in_array($transaction->payment_status, [
            PpobTransaction::PAYMENT_FAILED,
            PpobTransaction::PAYMENT_EXPIRED,
        ], true)) {
            $this->alertService->logWarning('PPOB payment reached non-success terminal state.', [
                'transaction_uuid' => $transaction->uuid,
                'payment_gateway_reference' => $transaction->payment_gateway_reference,
                'status' => $transaction->payment_status,
            ]);
        }
    }

    public function syncMidtransPayload(PpobTransaction $transaction, array $payload): void
    {
        $channel = $this->midtransClient->resolveChannelFromPayload($payload, $transaction->payment_channel_code);
        $paymentStatus = $this->resolvedMidtransPaymentStatus($payload);
        $grossAmount = (float) ($payload['gross_amount'] ?? $transaction->total_amount);
        $paymentCode = $this->midtransPaymentCode($payload);

        $transaction->forceFill([
            'payment_channel_name' => $channel['name'] ?? $transaction->payment_channel_name,
            'payment_channel_code' => $channel['code'] ?? $transaction->payment_channel_code,
            'total_amount' => $grossAmount,
            'amount_received' => match ($paymentStatus) {
                PpobTransaction::PAYMENT_PAID => $grossAmount,
                PpobTransaction::PAYMENT_REVERSED => (float) ($transaction->amount_received > 0 ? $transaction->amount_received : $grossAmount),
                default => 0,
            },
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === PpobTransaction::PAYMENT_PAID
                ? ($this->midtransPaidAt($payload) ?? $transaction->paid_at ?? now())
                : $transaction->paid_at,
            'expired_at' => $paymentStatus === PpobTransaction::PAYMENT_EXPIRED
                ? ($this->parseMidtransTimestamp($payload['expiry_time'] ?? null) ?? now())
                : $transaction->expired_at,
            'midtrans_order_id' => (string) ($payload['order_id'] ?? $transaction->midtrans_order_id),
            'midtrans_transaction_id' => (string) ($payload['transaction_id'] ?? $transaction->midtrans_transaction_id),
            'midtrans_payment_type' => (string) ($payload['payment_type'] ?? $transaction->midtrans_payment_type),
            'midtrans_expired_at' => $this->parseMidtransTimestamp($payload['expiry_time'] ?? null) ?? $transaction->midtrans_expired_at,
            'midtrans_payload' => $payload,
            'metadata' => $this->mergedMetadata($transaction, [
                'payment_gateway' => 'midtrans',
                'payment_code' => $paymentCode,
                'payment_actions' => data_get($payload, 'actions', []),
                'payment_instructions' => $this->midtransPaymentInstructions($payload),
                'payment_reversal_status' => $paymentStatus === PpobTransaction::PAYMENT_REVERSED
                    ? (string) ($payload['transaction_status'] ?? PpobTransaction::PAYMENT_REVERSED)
                    : data_get($transaction->metadata, 'payment_reversal_status'),
                'payment_reversal_at' => $paymentStatus === PpobTransaction::PAYMENT_REVERSED
                    ? (($this->parseMidtransTimestamp($payload['transaction_time'] ?? null) ?? now())->toIso8601String())
                    : data_get($transaction->metadata, 'payment_reversal_at'),
            ]),
        ])->save();

        if (in_array($transaction->payment_status, [
            PpobTransaction::PAYMENT_FAILED,
            PpobTransaction::PAYMENT_EXPIRED,
            PpobTransaction::PAYMENT_REVERSED,
        ], true)) {
            $this->alertService->logWarning('PPOB Midtrans payment reached non-success terminal state.', [
                'transaction_uuid' => $transaction->uuid,
                'midtrans_order_id' => $transaction->midtrans_order_id,
                'midtrans_transaction_id' => $transaction->midtrans_transaction_id,
                'status' => $transaction->payment_status,
            ]);
        }
    }

    public function dispatchFulfillment(string $uuid): void
    {
        if (config('services.ppob.fulfillment_dispatch') === 'sync') {
            ProcessPpobFulfillment::dispatchSync($uuid);

            return;
        }

        ProcessPpobFulfillment::dispatch($uuid)->afterCommit();
    }

    private function dispatchReconciliation(string $uuid): void
    {
        if (config('services.ppob.fulfillment_dispatch') === 'sync') {
            ReconcilePpobTransaction::dispatchSync($uuid);

            return;
        }

        ReconcilePpobTransaction::dispatch($uuid)->afterCommit();
    }

    private function paymentGateway(): string
    {
        return strtolower((string) config('services.ppob.payment_gateway', 'midtrans'));
    }

    private function resolvedMidtransPaymentStatus(array $payload): string
    {
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? 'pending'));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));

        return match (true) {
            $transactionStatus === 'settlement' => PpobTransaction::PAYMENT_PAID,
            $transactionStatus === 'capture' && ! in_array($fraudStatus, ['challenge', 'deny'], true) => PpobTransaction::PAYMENT_PAID,
            in_array($transactionStatus, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true) => PpobTransaction::PAYMENT_REVERSED,
            in_array($transactionStatus, ['deny', 'cancel', 'failure'], true) || $fraudStatus === 'deny' => PpobTransaction::PAYMENT_FAILED,
            $transactionStatus === 'expire' => PpobTransaction::PAYMENT_EXPIRED,
            default => PpobTransaction::PAYMENT_UNPAID,
        };
    }

    private function midtransPaymentInstructions(array $payload): array
    {
        $instructions = [];
        $vaNumbers = data_get($payload, 'va_numbers', []);

        if (is_array($vaNumbers)) {
            foreach ($vaNumbers as $vaNumber) {
                if (! is_array($vaNumber)) {
                    continue;
                }

                $bank = strtoupper((string) ($vaNumber['bank'] ?? 'BANK'));
                $number = (string) ($vaNumber['va_number'] ?? '');

                if ($number === '') {
                    continue;
                }

                $instructions[] = [
                    'title' => sprintf('%s Virtual Account', $bank),
                    'steps' => [
                        sprintf('Nomor virtual account: %s', $number),
                        'Selesaikan pembayaran melalui kanal bank terkait sebelum masa berlaku berakhir.',
                    ],
                ];
            }
        }

        if ($instructions === [] && filled($payload['permata_va_number'] ?? null)) {
            $instructions[] = [
                'title' => 'Permata Virtual Account',
                'steps' => [
                    sprintf('Nomor virtual account: %s', (string) $payload['permata_va_number']),
                    'Selesaikan pembayaran melalui Permata Bank sebelum masa berlaku berakhir.',
                ],
            ];
        }

        if (($payload['payment_type'] ?? null) === 'echannel' && filled($payload['bill_key'] ?? null)) {
            $instructions[] = [
                'title' => 'Mandiri Bill',
                'steps' => array_values(array_filter([
                    filled($payload['biller_code'] ?? null) ? sprintf('Biller code: %s', (string) $payload['biller_code']) : null,
                    sprintf('Bill key: %s', (string) $payload['bill_key']),
                    'Gunakan biller code dan bill key tersebut untuk menyelesaikan pembayaran.',
                ])),
            ];
        }

        if (($payload['payment_type'] ?? null) === 'cstore' && filled($payload['payment_code'] ?? null)) {
            $instructions[] = [
                'title' => 'Convenience Store',
                'steps' => [
                    sprintf('Payment code: %s', (string) $payload['payment_code']),
                    'Tunjukkan payment code ini saat membayar di kasir.',
                ],
            ];
        }

        $actions = data_get($payload, 'actions', []);

        if ($instructions === [] && is_array($actions) && $actions !== []) {
            $steps = [];

            foreach ($actions as $action) {
                if (! is_array($action) || blank($action['url'] ?? null)) {
                    continue;
                }

                $steps[] = (string) $action['url'];
            }

            if ($steps !== []) {
                $instructions[] = [
                    'title' => 'Payment Links',
                    'steps' => $steps,
                ];
            }
        }

        return $instructions;
    }

    private function midtransPaymentCode(array $payload): ?string
    {
        $vaNumber = data_get($payload, 'va_numbers.0.va_number');

        if (is_string($vaNumber) && $vaNumber !== '') {
            return $vaNumber;
        }

        foreach (['permata_va_number', 'payment_code', 'bill_key'] as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function midtransPaidAt(array $payload): ?Carbon
    {
        foreach (['settlement_time', 'transaction_time'] as $key) {
            $parsed = $this->parseMidtransTimestamp($payload[$key] ?? null);

            if ($parsed instanceof Carbon) {
                return $parsed;
            }
        }

        return null;
    }

    private function parseMidtransTimestamp(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value, config('app.timezone'))
                ->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function mergedMetadata(PpobTransaction $transaction, array $extra): array
    {
        $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];

        return array_merge($metadata, $extra);
    }

    private function midtransExpiryMinutes(): int
    {
        return max(1, (int) config('services.midtrans.expiry_minutes', 60));
    }

    private function reconcileSuccessfulPaymentWindowHours(): int
    {
        return max(1, (int) config('services.ppob.reconcile_success_window_hours', 24));
    }
}
