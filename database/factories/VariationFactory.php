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
            'color' => json_encode(['blue' => 4, 'red' => 5]),
            'quantity' => rand(1, 20),
            'owner_token' => Inventory::all()->random()->id
        ];
    }
}
