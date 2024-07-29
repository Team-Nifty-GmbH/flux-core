<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_bank_connection_id_foreign');
            $table->renameColumn('bank_connection_id', 'contact_bank_connection_id');
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_contact_bank_connection_id_foreign');
            $table->renameColumn('contact_bank_connection_id', 'bank_connection_id');
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections')
                ->nullOnDelete();
        });
    }
};
