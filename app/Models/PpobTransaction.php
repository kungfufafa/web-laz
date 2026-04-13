<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class PpobTransaction extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory, HasUuids;

    public const SERVICE_PREPAID = 'prepaid';

    public const SERVICE_POSTPAID = 'postpaid';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_EXPIRED = 'expired';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REVERSED = 'reversed';

    public const FULFILLMENT_PENDING = 'pending';

    public const FULFILLMENT_PROCESSING = 'processing';

    public const FULFILLMENT_SUCCEEDED = 'succeeded';

    public const FULFILLMENT_FAILED = 'failed';

    public const FULFILLMENT_MANUAL_REVIEW = 'manual_review';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'ppob_product_id',
        'ppob_pricing_rule_id',
        'provider',
        'service_type',
        'buyer_sku_code',
        'product_name',
        'category',
        'brand',
        'type',
        'customer_no',
        'customer_name',
        'inquiry_reference',
        'inquiry_payload',
        'inquiry_expires_at',
        'provider_price',
        'markup_amount',
        'base_price',
        'fee_customer',
        'fee_merchant',
        'total_amount',
        'amount_received',
        'payment_channel_code',
        'payment_channel_name',
        'payment_status',
        'paid_at',
        'expired_at',
        'fulfillment_status',
        'fulfillment_message',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_payment_type',
        'midtrans_expired_at',
        'midtrans_payload',
        'payment_gateway_reference',
        'payment_gateway_order_id',
        'payment_gateway_checkout_url',
        'payment_gateway_pay_url',
        'payment_gateway_pay_code',
        'payment_gateway_expired_at',
        'payment_gateway_payload',
        'digiflazz_ref_id',
        'digiflazz_status',
        'digiflazz_rc',
        'digiflazz_sn',
        'digiflazz_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'inquiry_payload' => 'array',
            'midtrans_payload' => 'array',
            'payment_gateway_payload' => 'array',
            'digiflazz_payload' => 'array',
            'metadata' => 'array',
            'provider_price' => 'decimal:2',
            'markup_amount' => 'decimal:2',
            'base_price' => 'decimal:2',
            'fee_customer' => 'decimal:2',
            'fee_merchant' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'midtrans_expired_at' => 'datetime',
            'payment_gateway_expired_at' => 'datetime',
            'inquiry_expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PpobProduct::class, 'ppob_product_id');
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PpobPricingRule::class, 'ppob_pricing_rule_id');
    }

    public function resolvedStatus(): string
    {
        return match (true) {
            $this->payment_status === self::PAYMENT_EXPIRED => 'expired',
            $this->payment_status === self::PAYMENT_REVERSED => 'payment_reversed',
            $this->payment_status === self::PAYMENT_FAILED => 'payment_failed',
            $this->payment_status !== self::PAYMENT_PAID => 'awaiting_payment',
            $this->fulfillment_status === self::FULFILLMENT_MANUAL_REVIEW => 'manual_review',
            $this->fulfillment_status === self::FULFILLMENT_SUCCEEDED => 'succeeded',
            $this->fulfillment_status === self::FULFILLMENT_FAILED => 'failed',
            default => 'processing',
        };
    }

    public function resolvedPaymentGateway(): string
    {
        $configuredGateway = strtolower((string) Arr::get($this->metadata, 'payment_gateway', ''));

        if (in_array($configuredGateway, ['midtrans', 'tripay'], true)) {
            return $configuredGateway;
        }

        if ($this->hasMidtransGatewayData()) {
            return 'midtrans';
        }

        if ($this->hasTripayGatewayData()) {
            return 'tripay';
        }

        return strtolower((string) config('services.ppob.payment_gateway', 'midtrans'));
    }

    public function shouldDispatchFulfillment(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID
            && in_array($this->fulfillment_status, [
                self::FULFILLMENT_PENDING,
                self::FULFILLMENT_FAILED,
            ], true);
    }

    private function hasMidtransGatewayData(): bool
    {
        return filled($this->midtrans_order_id)
            || filled($this->midtrans_transaction_id)
            || filled($this->midtrans_snap_token)
            || filled($this->midtrans_redirect_url)
            || filled($this->midtrans_payment_type)
            || filled($this->midtrans_expired_at)
            || filled($this->midtrans_payload);
    }

    private function hasTripayGatewayData(): bool
    {
        return filled($this->payment_gateway_reference)
            || filled($this->payment_gateway_checkout_url)
            || filled($this->payment_gateway_pay_url)
            || filled($this->payment_gateway_pay_code)
            || filled($this->payment_gateway_expired_at)
            || filled($this->payment_gateway_payload)
            || (filled($this->payment_gateway_order_id) && ! $this->hasMidtransGatewayData());
    }
}
