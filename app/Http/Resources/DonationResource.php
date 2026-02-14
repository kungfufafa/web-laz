<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'amount' => (float) $this->amount,
            'category' => $this->category,
            'payment_type' => $this->payment_type,
            'context_slug' => $this->context_slug,
            'context_label' => $this->context_label,
            'intention_note' => $this->intention_note,
            'calculator_type' => $this->calculator_type,
            'calculator_breakdown' => $this->calculator_breakdown,
            'status' => $this->status,
            'payment_method_id' => $this->payment_method_id,
            'payment_method_name' => $this->paymentMethod->name ?? null,
            'proof_image_url' => $this->proof_image ? \Illuminate\Support\Facades\Storage::url($this->proof_image) : null,
            'qris_payload' => $this->when(isset($this->qris_dynamic_payload), $this->qris_dynamic_payload),
            'donor_name' => $this->donor_name,
            'donor_phone' => $this->donor_phone,
            'donor_email' => $this->donor_email,
            'is_guest' => $this->user_id === null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
