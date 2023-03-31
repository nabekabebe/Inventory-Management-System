<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()
                ->unique()
                ->safeEmail(),
            'password' => bcrypt('nabek'),
            'phone_number' => fake()
                ->unique()
                ->phoneNumber(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'managing_token' => fake()->uuid(),
            'is_manager' => false
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                'email_verified_at' => null
            ]
        );
    }
}
