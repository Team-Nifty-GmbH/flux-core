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
        Schema::table('order_positions', function (Blueprint $table) {
            $table->foreignId('ledger_account_id')
                ->nullable()
                ->after('client_id')
                ->constrained('ledger_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign(['ledger_account_id']);
            $table->dropColumn('ledger_account_id');
        });
    }
};
