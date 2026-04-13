<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderCallbackLog>
 */
class ProviderCallbackLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => fake()->randomElement(['midtrans', 'tripay', 'digiflazz']),
            'event' => fake()->randomElement(['callback', 'inquiry', 'transaction']),
            'external_id' => fake()->uuid(),
            'signature' => fake()->sha256(),
            'is_valid_signature' => true,
            'headers' => ['Content-Type' => 'application/json'],
            'payload' => ['status' => 'success'],
            'processing_result' => fake()->randomElement(['processed', 'ignored', 'failed']),
            'processed_at' => now(),
        ];
    }
}
