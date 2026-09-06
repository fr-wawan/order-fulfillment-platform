<?php

use App\Models\Sku;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Warehouse::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Sku::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->timestamps();

            $table->unique(['warehouse_id', 'sku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
