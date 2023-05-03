<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Variation;
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
            'description' => fake()->sentence(),
            'barcode' => fake()
                ->unique()
                ->isbn13(),
            'brand' => fake()->word(),
            'manufacturer' => fake()->company(),
            'purchase_price' => strval(rand(500, 1000)),
            'sell_price' => strval(rand(500, 1000)),
            'category_id' => Category::all()->random()->id,
            'owner_token' => User::all()->random()->managing_token
        ];
    }
}
