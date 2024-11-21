<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bank_connection_client')) {
            return;
        }

        Schema::create('bank_connection_client', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index('bank_connection_client_client_id_foreign');
            $table->unsignedBigInteger('bank_connection_id')->index('bank_connection_client_bank_connection_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_connection_client');
    }
};
