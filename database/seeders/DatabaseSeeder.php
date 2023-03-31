<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Transfer;
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
        Schema::enableForeignKeyConstraints();

        // user seeder
        User::factory(10)->create();
        // category seeder
        Category::factory(10)->create();
        // warehouse seeder
        Warehouse::factory(10)->create();
        // inventory seeder
        Inventory::factory(10)->create();
        //warehouse info seeder
        WarehouseInfo::factory(15)->create();
        //transfers seeder
        Transfer::factory(10)->create();
        //transactions seeder
        Transaction::factory(10)->create();
    }
}
