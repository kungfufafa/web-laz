<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationPaymentType extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<self>> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'donation_category_id',
        'key',
        'label',
        'description',
        'is_zakat_calculator',
        'conditions',
        'sort_order',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'is_zakat_calculator' => 'boolean',
        'conditions' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $paymentType): void {
            if ($paymentType->is_locked) {
                throw new LogicException('Default donation payment type cannot be deleted.');
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DonationCategory::class, 'donation_category_id');
    }
}
