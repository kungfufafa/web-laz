<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberPrayer>
 */
class MemberPrayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content' => fake()->sentence(),
            'is_anonymous' => fake()->boolean(),
            'likes_count' => fake()->numberBetween(0, 100),
            'status' => 'pending',
        ];
    }
}
