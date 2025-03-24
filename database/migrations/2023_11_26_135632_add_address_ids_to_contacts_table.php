<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreignId('main_address_id')
                ->nullable()
                ->after('expense_ledger_account_id')
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('invoice_address_id')
                ->nullable()
                ->after('main_address_id')
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('delivery_address_id')
                ->nullable()
                ->after('invoice_address_id')
                ->constrained('addresses')
                ->nullOnDelete();
        });

        DB::statement('UPDATE contacts SET main_address_id = (
                SELECT id FROM addresses WHERE contact_id = contacts.id AND is_main_address = 1 LIMIT 1
            )'
        );
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('main_address_id');
            $table->dropConstrainedForeignId('invoice_address_id');
            $table->dropConstrainedForeignId('delivery_address_id');
        });
    }
};
