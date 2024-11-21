<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign(['bank_connection_id'], 'transactions_account_id_foreign')->references(['id'])->on('bank_connections')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['parent_id'])->references(['id'])->on('transactions')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_account_id_foreign');
            $table->dropForeign('transactions_currency_id_foreign');
            $table->dropForeign('transactions_order_id_foreign');
            $table->dropForeign('transactions_parent_id_foreign');
        });
    }
};
