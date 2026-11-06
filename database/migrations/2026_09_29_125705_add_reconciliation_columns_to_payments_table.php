<?php

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
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('next_reconciliation_at')->nullable();
            $table->timestamp('reconciliation_lease_until')->nullable();
            $table->string('reconciliation_lease_token')->nullable();
            $table->timestamp('last_reconciled_at')->nullable();
            $table->unsignedInteger('consecutive_reconciliation_failures')->default(0);
            $table->timestamp('reconciliation_stopped_at')->nullable();

            $table->index(['status', 'session_status', 'next_reconciliation_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
