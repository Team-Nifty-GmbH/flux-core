<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contact_tenant', function (Blueprint $table): void {
            $table->foreignId('expense_ledger_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contact_tenant', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('expense_ledger_account_id');
        });
    }
};
