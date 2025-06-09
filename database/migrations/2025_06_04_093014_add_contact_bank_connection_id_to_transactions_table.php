<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_bank_connection_id')
                ->nullable()
                ->after('bank_connection_id');
            $table->unsignedBigInteger('bank_connection_id')
                ->nullable()
                ->change();

            $table->dropForeign(['account_id']);
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections')
                ->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('bank_connections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('transactions')
            ->whereNull('bank_connection_id')
            ->delete();

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['bank_connection_id']);
            $table->dropForeign(['contact_bank_connection_id']);

            $table->dropColumn('contact_bank_connection_id');
            $table->unsignedBigInteger('bank_connection_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreign('bank_connection_id', 'transactions_account_id_foreign')
                ->references('id')
                ->on('bank_connections');
        });
    }
};
