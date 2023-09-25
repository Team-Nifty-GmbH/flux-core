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
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('approval_user_id')
                ->after('uuid')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('expense_ledger_account_id')
                ->after('client_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->onDelete('set null');
            $table->foreignId('vat_rate_id')
                ->after('expense_ledger_account_id')
                ->nullable()
                ->constrained('vat_rates')
                ->onDelete('set null');
            $table->string('vat_id')->after('credit_line')->nullable();
            $table->string('vendor_customer_number')
                ->after('vat_id')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('approval_user_id');
            $table->dropForeign('expense_ledger_account_id');
            $table->dropForeign('vat_rate_id');
            $table->dropColumn([
                'approval_user_id',
                'expense_ledger_account_id',
                'vat_rate_id',
                'vat_id',
                'vendor_customer_number'
            ]);
        });
    }
};
