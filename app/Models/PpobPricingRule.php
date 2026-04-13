<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpobPricingRule extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    public const MARKUP_FIXED = 'fixed';

    public const MARKUP_PERCENT = 'percent';

    protected $fillable = [
        'name',
        'service_type',
        'category',
        'brand',
        'buyer_sku_code',
        'markup_type',
        'markup_value',
        'min_markup',
        'max_markup',
        'rounding_unit',
        'priority',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'markup_value' => 'decimal:2',
            'min_markup' => 'decimal:2',
            'max_markup' => 'decimal:2',
            'rounding_unit' => 'integer',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(PpobProduct::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PpobTransaction::class);
    }

    public function specificity(): int
    {
        return count(array_filter([
            $this->service_type,
            $this->category,
            $this->brand,
            $this->buyer_sku_code,
        ], fn (mixed $value): bool => is_string($value) && trim($value) !== ''));
    }
}
