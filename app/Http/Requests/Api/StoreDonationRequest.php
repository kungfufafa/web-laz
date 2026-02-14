<?php

namespace App\Http\Requests\Api;

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
            $aliases = config('donation.category_aliases', []);
            $raw = strtolower(trim($this->input('category')));
            $this->merge([
                'category' => $aliases[$raw] ?? $raw,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user('sanctum');
        $categories = config('donation.categories', []);
        $allPaymentTypes = collect($categories)
            ->flatMap(fn (array $cat): array => array_keys($cat['payment_types']))
            ->unique()
            ->values()
            ->all();

        return [
            'amount' => 'required|numeric|min:10000',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'proof_image' => 'nullable|image|max:2048',
            'category' => ['required', Rule::in(array_keys($categories))],
            'payment_type' => ['required', Rule::in($allPaymentTypes)],
            'context_slug' => ['nullable', 'string', 'max:120'],
            'context_label' => ['nullable', 'string', 'max:120'],
            'intention_note' => ['nullable', 'string', 'max:255'],
            'calculator_type' => ['nullable', Rule::in(config('donation.zakat_calculator_types', []))],
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
                $categories = config('donation.categories', []);

                $allowedTypes = array_keys($categories[$category]['payment_types'] ?? []);
                if (! in_array($paymentType, $allowedTypes, true)) {
                    $validator->errors()->add(
                        'payment_type',
                        'The selected payment type does not match the category.',
                    );
                }

                if (
                    in_array($category, ['infak', 'sedekah'], true)
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
}
