<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('client_payment_type', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('client_payment_type', function (Blueprint $table) {
            $table->dropForeign('client_payment_type_client_id_foreign');
            $table->dropForeign('client_payment_type_payment_type_id_foreign');
        });
    }
};
