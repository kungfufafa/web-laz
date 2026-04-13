<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpobTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paymentGateway = $this->resolvedPaymentGateway();
        $paymentInstructions = data_get($this->metadata, 'payment_instructions', []);
        $payCode = data_get($this->metadata, 'payment_code')
            ?? $this->payment_gateway_pay_code
            ?? data_get($this->midtrans_payload, 'va_numbers.0.va_number')
            ?? data_get($this->midtrans_payload, 'permata_va_number')
            ?? data_get($this->midtrans_payload, 'payment_code')
            ?? data_get($this->midtrans_payload, 'bill_key');
        $payUrl = $this->payment_gateway_pay_url;

        if ($payUrl === null) {
            $midtransActions = data_get($this->metadata, 'payment_actions', data_get($this->midtrans_payload, 'actions', []));

            if (is_array($midtransActions)) {
                foreach ($midtransActions as $action) {
                    if (is_array($action) && is_string($action['url'] ?? null) && $action['url'] !== '') {
                        $payUrl = $action['url'];
                        break;
                    }
                }
            }
        }

        return [
            'id' => $this->uuid,
            'service_type' => $this->service_type,
            'provider' => $this->provider,
            'payment_gateway' => $paymentGateway,
            'buyer_sku_code' => $this->buyer_sku_code,
            'product_name' => $this->product_name,
            'category' => $this->category,
            'brand' => $this->brand,
            'type' => $this->type,
            'customer_no' => $this->customer_no,
            'customer_name' => $this->customer_name,
            'status' => $this->resolvedStatus(),
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'fulfillment_message' => $this->fulfillment_message,
            'provider_price' => (float) $this->provider_price,
            'markup_amount' => (float) $this->markup_amount,
            'base_price' => (float) $this->base_price,
            'fee_customer' => (float) $this->fee_customer,
            'fee_merchant' => (float) $this->fee_merchant,
            'total_amount' => (float) $this->total_amount,
            'amount_received' => (float) $this->amount_received,
            'pricing_rule_name' => $this->pricingRule?->name,
            'payment_channel_code' => $this->payment_channel_code,
            'payment_channel_name' => $this->payment_channel_name,
            'checkout_url' => $this->midtrans_redirect_url ?? $this->payment_gateway_checkout_url,
            'pay_url' => $payUrl,
            'pay_code' => is_string($payCode) ? $payCode : null,
            'payment_instructions' => is_array($paymentInstructions) ? $paymentInstructions : [],
            'payment_reference' => $this->midtrans_transaction_id ?? $this->payment_gateway_reference,
            'payment_order_id' => $this->midtrans_order_id ?? $this->payment_gateway_order_id,
            'midtrans_order_id' => $this->midtrans_order_id,
            'midtrans_transaction_id' => $this->midtrans_transaction_id,
            'midtrans_snap_token' => $this->midtrans_snap_token,
            'tripay_reference' => $this->payment_gateway_reference,
            'tripay_merchant_ref' => $this->payment_gateway_order_id,
            'digiflazz_ref_id' => $this->digiflazz_ref_id,
            'digiflazz_status' => $this->digiflazz_status,
            'digiflazz_rc' => $this->digiflazz_rc,
            'digiflazz_sn' => $this->digiflazz_sn,
            'expires_at' => ($this->midtrans_expired_at ?? $this->payment_gateway_expired_at)?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
