<?php

namespace App\Http\Requests\Api;

use App\Services\DonationCatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('category'))) {
            $raw = trim($this->input('category'));
            $normalizedCategory = $this->donationCatalog()->normalizeCategory($raw);
            $this->merge([
                'category' => $normalizedCategory ?? strtolower($raw),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user('sanctum');
        $catalog = $this->donationCatalog();
        $categories = $catalog->validCategoryKeys();
        $allPaymentTypes = $catalog->validPaymentTypeKeys();
        $calculatorTypes = $catalog->zakatCalculatorTypes();

        return [
            'amount' => 'required|numeric|min:10000',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'proof_image' => 'nullable|image|max:2048',
            'category' => ['required', Rule::in($categories)],
            'payment_type' => ['required', Rule::in($allPaymentTypes)],
            'context_slug' => ['nullable', 'string', 'max:120'],
            'context_label' => ['nullable', 'string', 'max:120'],
            'intention_note' => ['nullable', 'string', 'max:255'],
            'calculator_type' => ['nullable', Rule::in($calculatorTypes)],
            'calculator_breakdown' => ['nullable', 'array'],
            'guest_token' => ['nullable', 'string', 'max:120', Rule::requiredIf($user === null)],
            'donor_name' => ['nullable', 'string', 'max:120', Rule::requiredIf($user === null)],
            'donor_phone' => ['nullable', 'string', 'max:30', Rule::requiredIf($user === null)],
            'donor_email' => ['nullable', 'email', 'max:120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $data = $validator->validated();
                $category = $data['category'];
                $paymentType = $data['payment_type'];
                $catalog = $this->donationCatalog();

                $allowedTypes = $catalog->validPaymentTypesForCategory($category);
                if (! in_array($paymentType, $allowedTypes, true)) {
                    $validator->errors()->add(
                        'payment_type',
                        'The selected payment type does not match the category.',
                    );
                }

                if (
                    $catalog->requiresContext($category)
                    && $catalog->hasContextOptions($category)
                    && blank($data['context_label'] ?? null)
                ) {
                    $validator->errors()->add(
                        'context_label',
                        'Please choose a donation context before continuing.',
                    );
                }

                if (
                    $category === 'zakat'
                    && isset($data['calculator_type'])
                    && $data['calculator_type'] !== $paymentType
                ) {
                    $validator->errors()->add(
                        'calculator_type',
                        'Calculator type does not match selected zakat type.',
                    );
                }

                $conditions = $catalog->paymentTypeConditions($category, $paymentType);

                $minAmount = $conditions['min_amount'] ?? null;
                if (is_numeric($minAmount) && (float) $data['amount'] < (float) $minAmount) {
                    $validator->errors()->add(
                        'amount',
                        sprintf('Minimum donation amount for this type is %s.', number_format((float) $minAmount, 0, ',', '.')),
                    );
                }

                $maxAmount = $conditions['max_amount'] ?? null;
                if (is_numeric($maxAmount) && (float) $data['amount'] > (float) $maxAmount) {
                    $validator->errors()->add(
                        'amount',
                        sprintf('Maximum donation amount for this type is %s.', number_format((float) $maxAmount, 0, ',', '.')),
                    );
                }

                if (($conditions['require_context'] ?? false) && blank($data['context_label'] ?? null)) {
                    $validator->errors()->add(
                        'context_label',
                        'This donation type requires context information.',
                    );
                }

                if (($conditions['require_intention_note'] ?? false) && blank($data['intention_note'] ?? null)) {
                    $validator->errors()->add(
                        'intention_note',
                        'This donation type requires an intention note.',
                    );
                }
            },
        ];
    }

    /**
     * Return the normalized category.
     */
    public function normalizedCategory(): string
    {
        return $this->input('category');
    }

    private function donationCatalog(): DonationCatalogService
    {
        return app(DonationCatalogService::class);
    }
}
