<?php

namespace App\Http\Requests\Api;

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Support\PpobCustomerInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePpobTransactionRequest extends FormRequest
{
    private ?PpobProduct $resolvedPrepaidProduct = null;

    private bool $resolvedPrepaidProductLoaded = false;

    protected function prepareForValidation(): void
    {
        $paymentChannelCode = $this->input('payment_channel_code', $this->input('tripay_method'));

        if (is_string($paymentChannelCode) && trim($paymentChannelCode) !== '') {
            $this->merge([
                'payment_channel_code' => trim($paymentChannelCode),
            ]);
        }
    }

    protected function passedValidation(): void
    {
        $product = $this->resolvedPrepaidProduct();

        if (! $product instanceof PpobProduct) {
            return;
        }

        $customerNo = $this->input('customer_no');

        if (! is_string($customerNo) || trim($customerNo) === '') {
            return;
        }

        $this->merge([
            'customer_no' => PpobCustomerInput::normalize($customerNo, $product),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::in([PpobTransaction::SERVICE_PREPAID, PpobTransaction::SERVICE_POSTPAID])],
            'payment_channel_code' => ['required', 'string', 'max:100'],
            'tripay_method' => ['nullable', 'string', 'max:100'],
            'buyer_sku_code' => ['nullable', 'string', 'max:100'],
            'customer_no' => ['nullable', 'string', 'max:120'],
            'inquiry_reference' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $serviceType = $this->input('service_type');

                if ($serviceType === PpobTransaction::SERVICE_PREPAID) {
                    if (! is_string($this->input('buyer_sku_code')) || trim($this->input('buyer_sku_code')) === '') {
                        $validator->errors()->add('buyer_sku_code', 'buyer_sku_code is required for prepaid transactions.');
                    }

                    if (! is_string($this->input('customer_no')) || trim($this->input('customer_no')) === '') {
                        $validator->errors()->add('customer_no', 'customer_no is required for prepaid transactions.');
                    }

                    $product = $this->resolvedPrepaidProduct();

                    if (! $product instanceof PpobProduct) {
                        $validator->errors()->add('buyer_sku_code', 'Selected prepaid product is not available.');

                        return;
                    }

                    $customerNoValidationMessage = PpobCustomerInput::validationMessage(
                        (string) $this->input('customer_no'),
                        $product,
                    );

                    if (is_string($customerNoValidationMessage) && $customerNoValidationMessage !== '') {
                        $validator->errors()->add('customer_no', $customerNoValidationMessage);
                    }
                }

                if ($serviceType === PpobTransaction::SERVICE_POSTPAID) {
                    if (! is_string($this->input('inquiry_reference')) || trim($this->input('inquiry_reference')) === '') {
                        $validator->errors()->add('inquiry_reference', 'inquiry_reference is required for postpaid transactions.');
                    }
                }
            },
        ];
    }

    private function resolvedPrepaidProduct(): ?PpobProduct
    {
        if ($this->resolvedPrepaidProductLoaded) {
            return $this->resolvedPrepaidProduct;
        }

        $this->resolvedPrepaidProductLoaded = true;

        if ($this->input('service_type') !== PpobTransaction::SERVICE_PREPAID) {
            return null;
        }

        $buyerSkuCode = $this->input('buyer_sku_code');

        if (! is_string($buyerSkuCode) || trim($buyerSkuCode) === '') {
            return null;
        }

        $this->resolvedPrepaidProduct = PpobProduct::query()
            ->where('service_type', PpobTransaction::SERVICE_PREPAID)
            ->where('buyer_sku_code', $buyerSkuCode)
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true)
            ->first();

        return $this->resolvedPrepaidProduct;
    }
}
