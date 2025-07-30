<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumns(
            'contacts',
            [
                'delivery_address_id',
                'invoice_address_id',
                'main_address_id',
            ])
        ) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('delivery_address_id')
                ->nullable()
                ->after('currency_id')
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('invoice_address_id')
                ->nullable()
                ->after('expense_ledger_account_id')
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('main_address_id')
                ->nullable()
                ->after('invoice_address_id')
                ->constrained('addresses')
                ->nullOnDelete();
        });
    }

    public function down(): void {}
};
