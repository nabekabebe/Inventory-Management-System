<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Transfer;
use App\Models\Variation;
use App\Models\WarehouseInfo;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Inventory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // truncate and clear db first
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Warehouse::truncate();
        Category::truncate();
        Inventory::truncate();
        Transaction::truncate();
        WarehouseInfo::truncate();
        Variation::truncate();
        Schema::enableForeignKeyConstraints();

        // user seeder
        $user = User::factory(10)
            ->create(['id' => null])
            ->first();
        $user->update(['is_manager' => 1, 'email' => 'nabek@gmail.com']);
        // category seeder
        Category::factory(10)->create(['owner_token' => $user->managing_token]);
        // warehouse seeder
        Warehouse::factory(10)->create([
            'owner_token' => $user->managing_token
        ]);
        // inventory seeder
        Inventory::factory(10)
            ->create(['owner_token' => $user->managing_token])
            ->each(function ($inventory) {
                //variation
                Variation::factory(2)->create([
                    'inventory_id' => $inventory->id
                ]);
            });
        //warehouse info seeder
        WarehouseInfo::factory(15)->create();
        //transfers seeder
        Transfer::factory(10)->create(['owner_token' => $user->managing_token]);
        //transactions seeder
        Transaction::factory(10)->create([
            'owner_token' => $user->managing_token
        ]);
    }
}
