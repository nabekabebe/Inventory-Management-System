<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\WarehouseInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseInfo>
 */
class WarehouseInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'quantity' => rand(1, 100),
            'sell_count' => 0,
            'refund_count' => 0,
            'warehouse_id' => Warehouse::all()->random()->id,
            'inventory_id' => Inventory::all()->random()->id
        ];
    }
}
