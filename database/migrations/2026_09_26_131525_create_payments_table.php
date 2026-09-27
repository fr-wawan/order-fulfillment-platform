<?php

use App\Models\Order;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->restrictOnDelete()
                ->unique();
            $table->string('provider_payment_id')
                ->nullable()
                ->unique();
            $table->unsignedBigInteger('amount');
            $table->timestamp('paid_at')->nullable();
            $table->string('provider_session_id')
                ->nullable()
                ->unique();
            $table->text('checkout_url')
                ->nullable();
            $table->string('status');
            $table->string('session_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
