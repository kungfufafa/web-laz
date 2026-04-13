<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'account_holder',
        'logo',
        'qris_static_payload',
        'qris_image',
        'is_active',
        'is_primary',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $paymentMethod): void {
            if (! $paymentMethod->is_primary) {
                return;
            }

            static::query()
                ->whereKeyNot($paymentMethod->getKey())
                ->where('is_primary', true)
                ->update([
                    'is_primary' => false,
                ]);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedForCheckout(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_primary')
            ->orderByRaw('CASE WHEN LOWER(name) LIKE ? THEN 1 ELSE 0 END DESC', ['%midtrans%'])
            ->orderBy('name');
    }

    public function donations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
