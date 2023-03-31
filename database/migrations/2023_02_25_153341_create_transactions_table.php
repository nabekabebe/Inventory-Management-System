<?php

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Inventory;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->timestamp('created_at');
            $table->string('comment')->nullable();
            $table->enum('payment_method', ['bank', 'cash'])->default('cash');
            $table->boolean('is_active')->default(true);
            $table->string('owner_token');
            $table->enum('transaction_type', ['sold', 'refunded']);
            $table
                ->foreignIdFor(Inventory::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(Warehouse::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(User::class)
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
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
