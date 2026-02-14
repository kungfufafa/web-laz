<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(['zakat', 'infak', 'sedekah']);
        $paymentType = match ($category) {
            'zakat' => fake()->randomElement(['maal', 'fitrah', 'profesi']),
            'infak' => fake()->randomElement(['kemanusiaan', 'umum']),
            default => fake()->randomElement(['jariyah', 'umum']),
        };

        return [
            'user_id' => \App\Models\User::factory(),
            'guest_token' => null,
            'donor_name' => fake()->name(),
            'donor_phone' => fake()->phoneNumber(),
            'donor_email' => fake()->safeEmail(),
            'payment_method_id' => \App\Models\PaymentMethod::factory(),
            'amount' => fake()->randomFloat(2, 10000, 1000000),
            'category' => $category,
            'payment_type' => $paymentType,
            'context_slug' => null,
            'context_label' => null,
            'intention_note' => null,
            'calculator_type' => null,
            'calculator_breakdown' => null,
            'proof_image' => null,
            'status' => 'pending',
            'admin_note' => null,
        ];
    }
}
