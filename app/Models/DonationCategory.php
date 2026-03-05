<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationCategory extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<self>> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'label',
        'description',
        'requires_context',
        'sort_order',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'requires_context' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $category): void {
            if ($category->is_locked) {
                throw new LogicException('Default donation category cannot be deleted.');
            }
        });
    }

    public function paymentTypes(): HasMany
    {
        return $this->hasMany(DonationPaymentType::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
