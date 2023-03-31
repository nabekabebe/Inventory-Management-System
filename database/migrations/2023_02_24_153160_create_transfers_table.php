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
            $table->id();
            $table->integer('quantity');
            $table->timestamp('created_at');
            $table->string('owner_token');
            $table
                ->foreignIdFor(Inventory::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId('source_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();
            $table
                ->foreignId('destination_id')
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
