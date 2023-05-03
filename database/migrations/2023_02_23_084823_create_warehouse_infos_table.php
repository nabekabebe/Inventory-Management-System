<?php

use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->integer('sell_count')->default(0);
            $table->integer('refund_count')->default(0);
            $table->integer('quantity');
            $table
                ->foreignUuid('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignUuid('inventory_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_infos');
    }
};
