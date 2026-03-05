<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'account_number' => $this->account_number,
            'account_holder' => $this->account_holder,
            'logo_url' => MediaUrl::resolve($request, $this->logo),
            'qris_image_url' => MediaUrl::resolve($request, $this->qris_image),
            'qris_static_payload' => $this->when(
                filled($this->qris_static_payload),
                $this->qris_static_payload
            ),
            'has_qris_template' => filled($this->qris_static_payload),
        ];
    }
}
