<?php

namespace App\Http\Requests\Api;

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Support\PpobCustomerInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePpobInquiryRequest extends FormRequest
{
    private ?PpobProduct $resolvedPostpaidProduct = null;

    private bool $resolvedPostpaidProductLoaded = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function passedValidation(): void
    {
        $product = $this->resolvedPostpaidProduct();

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

    public function rules(): array
    {
        return [
            'buyer_sku_code' => ['required', 'string', 'max:100'],
            'customer_no' => ['required', 'string', 'max:120'],
            'extra_fields' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $product = $this->resolvedPostpaidProduct();

                if (! $product instanceof PpobProduct) {
                    $validator->errors()->add('buyer_sku_code', 'Selected postpaid product is not available.');

                    return;
                }

                $customerNoValidationMessage = PpobCustomerInput::validationMessage(
                    (string) $this->input('customer_no'),
                    $product,
                );

                if (is_string($customerNoValidationMessage) && $customerNoValidationMessage !== '') {
                    $validator->errors()->add('customer_no', $customerNoValidationMessage);
                }
            },
        ];
    }

    private function resolvedPostpaidProduct(): ?PpobProduct
    {
        if ($this->resolvedPostpaidProductLoaded) {
            return $this->resolvedPostpaidProduct;
        }

        $this->resolvedPostpaidProductLoaded = true;

        $buyerSkuCode = $this->input('buyer_sku_code');

        if (! is_string($buyerSkuCode) || trim($buyerSkuCode) === '') {
            return null;
        }

        $this->resolvedPostpaidProduct = PpobProduct::query()
            ->where('service_type', PpobTransaction::SERVICE_POSTPAID)
            ->where('buyer_sku_code', $buyerSkuCode)
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true)
            ->first();

        return $this->resolvedPostpaidProduct;
    }
}
