<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('bank_connection_client', function (Blueprint $table) {
            $table->foreign(['bank_connection_id'])->references(['id'])->on('bank_connections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('bank_connection_client', function (Blueprint $table) {
            $table->dropForeign('bank_connection_client_bank_connection_id_foreign');
            $table->dropForeign('bank_connection_client_client_id_foreign');
        });
    }
};
