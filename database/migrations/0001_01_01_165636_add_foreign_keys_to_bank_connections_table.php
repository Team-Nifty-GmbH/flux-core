<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_connections', function (Blueprint $table) {
            $table->foreign(['currency_id'], 'accounts_currency_id_foreign')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ledger_account_id'])->references(['id'])->on('ledger_accounts')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bank_connections', function (Blueprint $table) {
            $table->dropForeign('accounts_currency_id_foreign');
            $table->dropForeign('bank_connections_ledger_account_id_foreign');
        });
    }
};
