<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_payment_type')) {
            return;
        }

        Schema::create('client_payment_type', function (Blueprint $table) {
            $table->bigIncrements('pivot_id');
            $table->unsignedBigInteger('client_id')->index('client_payment_type_client_id_foreign');
            $table->unsignedBigInteger('payment_type_id')->index('client_payment_type_payment_type_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_type');
    }
};
