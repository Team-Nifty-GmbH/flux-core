<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->foreignId('lay_out_user_id')
                ->nullable()
                ->after('currency_id')
                ->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign(['lay_out_user_id']);
            $table->dropColumn('lay_out_user_id');
        });
    }
};
