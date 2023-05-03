<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => rand(1, 20),
            'inventory_id' => Inventory::all()->random()->id,
            'source_id' => Warehouse::all()->random()->id,
            'destination_id' => Warehouse::all()->random()->id,
            'owner_token' => User::all()->random()->managing_token
        ];
    }
}
