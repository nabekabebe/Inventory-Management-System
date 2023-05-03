<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Variation>
 */
class VariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $size = ['S', 'M', 'L', 'XL', 'XXL'];
        return [
            'size' => $size[rand(0, 4)],
            'color' => json_encode(['blue' => rand(1, 4), 'red' => rand(1, 7)]),
            'quantity' => rand(1, 20),
            'inventory_id' => Inventory::all()->random()->id
        ];
    }
}
