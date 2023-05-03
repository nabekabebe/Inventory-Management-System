<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Inventory;
use App\Models\Warehouse;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('quantity');
            $table->timestamps();
            $table->string('owner_token');
            $table
                ->foreignUuid('inventory_id')
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignUuid('source_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();
            $table
                ->foreignUuid('destination_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();
            $table->index('owner_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
};
