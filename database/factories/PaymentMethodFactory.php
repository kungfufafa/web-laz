<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['bank_transfer', 'ewallet', 'qris']);

        return [
            'name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI', 'GoPay', 'OVO']),
            'type' => $type,
            'account_number' => fake()->bankAccountNumber(),
            'account_holder' => fake()->name(),
            'logo' => null,
            'qris_static_payload' => $type === 'qris'
                ? '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18'
                : null,
            'qris_image' => null,
            'is_active' => true,
        ];
    }
}
