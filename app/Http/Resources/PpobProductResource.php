<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpobProductResource extends JsonResource
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
            'service_type' => $this->service_type,
            'category' => $this->category,
            'brand' => $this->brand,
            'type' => $this->type,
            'product_name' => $this->product_name,
            'seller_name' => $this->seller_name,
            'buyer_sku_code' => $this->buyer_sku_code,
            'price' => $this->resolvedSellPrice(),
            'provider_price' => $this->resolvedProviderPrice(),
            'sell_price' => $this->resolvedSellPrice(),
            'markup_amount' => $this->markup_amount !== null ? (float) $this->markup_amount : 0.0,
            'admin' => $this->admin,
            'commission' => $this->commission,
            'buyer_product_status' => (bool) $this->buyer_product_status,
            'seller_product_status' => (bool) $this->seller_product_status,
            'unlimited_stock' => $this->unlimited_stock,
            'stock' => $this->stock,
            'multi' => $this->multi,
            'start_cut_off' => $this->start_cut_off,
            'end_cut_off' => $this->end_cut_off,
            'description' => $this->description,
            'is_available' => $this->isAvailable(),
            'pricing_rule_name' => $this->pricingRule?->name,
            'synced_at' => $this->synced_at?->toIso8601String(),
        ];
    }
}
