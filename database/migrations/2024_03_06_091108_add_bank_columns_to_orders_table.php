<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('commission');
            $table->string('account_holder')->nullable()->after('iban');
            $table->string('bank_name')->nullable()->after('account_holder');
            $table->string('bic')->nullable()->after('bank_name');

            $table->dropUnique('orders_invoice_number_client_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['iban', 'account_holder', 'bank_name', 'bic']);

            $table->unique(['invoice_number', 'client_id']);
        });
    }
};
