<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\User;
use App\Models\WarehouseInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'identifier' => fake()
                ->unique()
                ->word(),
            'quantity' => rand(1, 100),
            'low_stock_trigger' => rand(5, 30),
            'description' => fake()->sentence(),
            'barcode' => fake()
                ->unique()
                ->isbn13(),
            'brand' => fake()->word(),
            'manufacturer' => fake()->company(),
            'purchase_price' => strval(rand(500, 1000)),
            'sell_price' => strval(rand(500, 1000)),
            'created_at' => now(),
            'category_id' => Category::all()->random()->id,
            'owner_token' => User::all()->random()->managing_token
        ];
    }
}
