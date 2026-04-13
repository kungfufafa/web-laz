<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpobProduct extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'service_type',
        'category',
        'brand',
        'type',
        'product_name',
        'seller_name',
        'buyer_sku_code',
        'ppob_pricing_rule_id',
        'provider_price',
        'provider_admin',
        'provider_commission',
        'sell_price',
        'markup_amount',
        'price',
        'admin',
        'commission',
        'buyer_product_status',
        'seller_product_status',
        'unlimited_stock',
        'stock',
        'multi',
        'start_cut_off',
        'end_cut_off',
        'description',
        'metadata',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'markup_amount' => 'decimal:2',
            'price' => 'decimal:2',
            'buyer_product_status' => 'boolean',
            'seller_product_status' => 'boolean',
            'unlimited_stock' => 'boolean',
            'multi' => 'boolean',
            'metadata' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PpobTransaction::class);
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PpobPricingRule::class, 'ppob_pricing_rule_id');
    }

    public function isAvailable(): bool
    {
        return $this->buyer_product_status && $this->seller_product_status;
    }

    public function resolvedProviderPrice(): ?float
    {
        if ($this->provider_price !== null) {
            return (float) $this->provider_price;
        }

        return $this->price !== null ? (float) $this->price : null;
    }

    public function resolvedSellPrice(): ?float
    {
        if ($this->sell_price !== null) {
            return (float) $this->sell_price;
        }

        return $this->price !== null ? (float) $this->price : null;
    }
}
