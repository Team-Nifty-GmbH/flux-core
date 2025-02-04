<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->after('uuid')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->dropUnique('ledger_accounts_number_unique');
        });

        DB::table('ledger_accounts')
            ->update([
                'client_id' => DB::table('clients')
                    ->where('is_default', true)
                    ->value('id'),
            ]);

        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable(false)
                ->change();
            $table->unique(['number', 'ledger_account_type_enum', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropUnique(['number', 'ledger_account_type_enum', 'client_id']);
            $table->unique('number');
        });
    }
};
