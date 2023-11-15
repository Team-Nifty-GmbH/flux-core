<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('account_id', 'bank_connection_id');
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('bank_connections');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('bank_connection_id', 'account_id');
            $table->dropForeign('transactions_bank_connection_id_foreign');
        });
    }
};
