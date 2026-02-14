<?php

namespace App\Models;

use App\Support\YouTube;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'youtube_id',
        'description',
        'thumbnail',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected function youtubeId(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => YouTube::extractVideoId($value) ?? $value,
            set: fn (?string $value): string => YouTube::extractVideoId($value) ?? trim((string) $value),
        );
    }
}
