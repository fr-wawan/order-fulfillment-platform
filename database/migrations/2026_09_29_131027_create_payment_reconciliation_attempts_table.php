<?php

use App\Models\Payment;
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
        Schema::create('payment_reconciliation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Payment::class)
                ->constrained()
                ->restrictOnDelete();
            $table->string('lease_token');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('outcome');
            $table->string('provider_status')->nullable();
            $table->string('error_type')->nullable();
            $table->string('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reconciliation_attempts');
    }
};
