<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment' => fake()->sentence(),
            'payment_method' => fake()->randomElement(['bank', 'cash']),
            'quantity' => rand(1, 5),
            'inventory_id' => Inventory::all()->random()->id,
            'warehouse_id' => Warehouse::all()->random()->id,
            'user_id' => User::all()->random()->id,
            'transaction_type' => fake()->randomElement(['sold', 'refunded']),
            'owner_token' => User::all()->random()->managing_token
        ];
    }
}
