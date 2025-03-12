<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::rename('accounts', 'bank_connections');

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropForeign('accounts_bank_connection_id_foreign');
            $table->dropColumn(['bank_connection_id', 'type', 'account_number']);
            $table->foreignId('ledger_account_id')
                ->after('currency_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();
            $table->string('iban')->unique()->nullable()->change();
            $table->string('bank_name')->nullable()->after('account_holder');
            $table->string('bic')->nullable()->after('iban');
            $table->integer('credit_limit')->nullable()->after('bic');
            $table->boolean('is_active')->default(true)->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::rename('bank_connections', 'accounts');

        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropForeign('bank_connections_ledger_account_id_foreign');
            $table->dropColumn(['ledger_account_id', 'bank_name', 'bic', 'credit_limit', 'is_active']);
            $table->dropIndex('bank_connections_iban_unique');
            $table->unsignedBigInteger('bank_connection_id')->nullable()->after('id');
            $table->string('account_number')->nullable()->after('name');
            $table->string('type')->nullable()->after('iban');

            $table->foreign('bank_connection_id')->references('id')->on('contact_bank_connections');
        });
    }
};
