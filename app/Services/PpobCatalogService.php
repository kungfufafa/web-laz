<?php

namespace App\Services;

use App\Models\PpobProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PpobCatalogService
{
    public function __construct(
        private readonly DigiflazzClient $digiflazzClient,
        private readonly PpobPricingService $pricingService,
    ) {
        //
    }

    public function paginateProducts(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->queryProducts($filters)
            ->orderBy('service_type')
            ->orderBy('category')
            ->orderBy('brand')
            ->orderBy('product_name')
            ->paginate($perPage);
    }

    public function syncFromDigiflazz(string $serviceType): int
    {
        $command = $serviceType === 'postpaid' ? 'pasca' : 'prepaid';
        $products = $this->digiflazzClient->fetchPriceList($command);
        $synced = 0;
        $syncedBuyerSkuCodes = [];

        foreach ($products as $product) {
            if (! is_array($product) || blank($product['buyer_sku_code'] ?? null)) {
                continue;
            }

            $buyerSkuCode = (string) $product['buyer_sku_code'];
            $syncedBuyerSkuCodes[] = $buyerSkuCode;

            $pricing = $this->pricingService->resolvePricing([
                'service_type' => $serviceType,
                'category' => isset($product['category']) ? (string) $product['category'] : null,
                'brand' => isset($product['brand']) ? (string) $product['brand'] : null,
                'buyer_sku_code' => $buyerSkuCode,
            ], isset($product['price']) ? (float) $product['price'] : 0.0);

            PpobProduct::query()->updateOrCreate(
                [
                    'provider' => 'digiflazz',
                    'buyer_sku_code' => $buyerSkuCode,
                ],
                [
                    'service_type' => $serviceType,
                    'category' => isset($product['category']) ? (string) $product['category'] : null,
                    'brand' => isset($product['brand']) ? (string) $product['brand'] : null,
                    'type' => isset($product['type']) ? (string) $product['type'] : null,
                    'product_name' => (string) ($product['product_name'] ?? $product['buyer_sku_code']),
                    'seller_name' => isset($product['seller_name']) ? (string) $product['seller_name'] : null,
                    'ppob_pricing_rule_id' => $pricing['rule']?->id,
                    'provider_price' => $pricing['provider_price'],
                    'provider_admin' => isset($product['admin']) ? (int) $product['admin'] : null,
                    'provider_commission' => isset($product['commission']) ? (int) $product['commission'] : null,
                    'sell_price' => $pricing['sell_price'],
                    'markup_amount' => $pricing['markup_amount'],
                    'price' => $pricing['provider_price'],
                    'admin' => isset($product['admin']) ? (int) $product['admin'] : null,
                    'commission' => isset($product['commission']) ? (int) $product['commission'] : null,
                    'buyer_product_status' => (bool) ($product['buyer_product_status'] ?? true),
                    'seller_product_status' => (bool) ($product['seller_product_status'] ?? true),
                    'unlimited_stock' => array_key_exists('unlimited_stock', $product) ? (bool) $product['unlimited_stock'] : null,
                    'stock' => isset($product['stock']) ? (int) $product['stock'] : null,
                    'multi' => array_key_exists('multi', $product) ? (bool) $product['multi'] : null,
                    'start_cut_off' => isset($product['start_cut_off']) ? (string) $product['start_cut_off'] : null,
                    'end_cut_off' => isset($product['end_cut_off']) ? (string) $product['end_cut_off'] : null,
                    'description' => isset($product['desc']) ? (string) $product['desc'] : null,
                    'metadata' => $product,
                    'synced_at' => now(),
                ],
            );

            $synced++;
        }

        if ($syncedBuyerSkuCodes === []) {
            return $synced;
        }

        $missingProductsQuery = PpobProduct::query()
            ->where('provider', 'digiflazz')
            ->where('service_type', $serviceType);
        $missingProductsQuery->whereNotIn('buyer_sku_code', array_values(array_unique($syncedBuyerSkuCodes)));

        $missingProductsQuery->update([
            'buyer_product_status' => false,
            'seller_product_status' => false,
            'synced_at' => now(),
        ]);

        return $synced;
    }

    private function queryProducts(array $filters = []): Builder
    {
        return PpobProduct::query()
            ->when(
                isset($filters['service_type']) && in_array($filters['service_type'], ['prepaid', 'postpaid'], true),
                fn (Builder $query): Builder => $query->where('service_type', $filters['service_type']),
            )
            ->when(
                is_string($filters['category'] ?? null) && trim($filters['category']) !== '',
                fn (Builder $query): Builder => $query->where('category', trim((string) $filters['category'])),
            )
            ->when(
                is_string($filters['brand'] ?? null) && trim($filters['brand']) !== '',
                fn (Builder $query): Builder => $query->where('brand', trim((string) $filters['brand'])),
            )
            ->when(
                is_string($filters['type'] ?? null) && trim($filters['type']) !== '',
                fn (Builder $query): Builder => $query->where('type', trim((string) $filters['type'])),
            )
            ->when(
                is_string($filters['search'] ?? null) && trim($filters['search']) !== '',
                function (Builder $query) use ($filters): Builder {
                    $search = trim((string) $filters['search']);

                    return $query->where(function (Builder $inner) use ($search): void {
                        $inner
                            ->where('product_name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('buyer_sku_code', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                },
            )
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true);
    }
}
