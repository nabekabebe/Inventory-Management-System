<?php

use App\Models\WarehouseInfo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Category;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique();
            $table->string('description');
            $table->timestamps();
            $table->integer('quantity');
            $table
                ->string('barcode')
                ->unique()
                ->nullable();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->integer('purchase_price');
            $table->integer('sell_price');
            $table->string('owner_token');
            $table->integer('low_stock_trigger')->default(10);
            $table
                ->foreignIdFor(Category::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->index('owner_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
