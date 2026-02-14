<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPrayer extends Model
{
    /** @use HasFactory<\Database\Factories\MemberPrayerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'is_anonymous',
        'likes_count',
        'status',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supports(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prayer_supports');
    }
}
