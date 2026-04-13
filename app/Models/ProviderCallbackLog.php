<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class ProviderCallbackLog extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    use MassPrunable;

    /**
     * Number of months to retain callback logs.
     */
    public const RETENTION_MONTHS = 3;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }

    protected $fillable = [
        'provider',
        'event',
        'external_id',
        'signature',
        'is_valid_signature',
        'headers',
        'payload',
        'processing_result',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_valid_signature' => 'boolean',
            'headers' => 'array',
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
