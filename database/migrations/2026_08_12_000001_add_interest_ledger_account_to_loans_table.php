<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->foreignId('interest_ledger_account_id')
                ->nullable()
                ->after('ledger_account_id')
                ->constrained('ledger_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('interest_ledger_account_id');
        });
    }
};
