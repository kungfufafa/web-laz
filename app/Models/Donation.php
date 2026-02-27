<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Donation extends Model
{
    /** @use HasFactory<\Database\Factories\DonationFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public const PROOF_IMAGE_DISK = 'local';

    public const PROOF_IMAGE_DIRECTORY = 'proofs';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'guest_token',
        'donor_name',
        'donor_phone',
        'donor_email',
        'payment_method_id',
        'amount',
        'category',
        'payment_type',
        'context_slug',
        'context_label',
        'intention_note',
        'calculator_type',
        'calculator_breakdown',
        'proof_image',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'calculator_breakdown' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function resolveProofImageUrl(?\DateTimeInterface $expiration = null): ?string
    {
        if (blank($this->proof_image)) {
            return null;
        }

        if (filter_var($this->proof_image, FILTER_VALIDATE_URL) !== false) {
            return $this->proof_image;
        }

        $expiration ??= now()->addMinutes(30);

        foreach ($this->proofImageDiskCandidates() as $diskName) {
            $proofImageUrl = $this->resolveProofImageUrlFromDisk($diskName, $expiration);

            if (filled($proofImageUrl)) {
                return $proofImageUrl;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function proofImageDiskCandidates(): array
    {
        return array_values(array_filter(
            array_unique([
                self::PROOF_IMAGE_DISK,
            ]),
            fn (mixed $diskName): bool => is_string($diskName) && filled($diskName),
        ));
    }

    private function resolveProofImageUrlFromDisk(string $diskName, \DateTimeInterface $expiration): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        try {
            if (! $disk->exists($this->proof_image)) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        try {
            return $disk->temporaryUrl($this->proof_image, $expiration);
        } catch (Throwable) {
            return null;
        }
    }
}
