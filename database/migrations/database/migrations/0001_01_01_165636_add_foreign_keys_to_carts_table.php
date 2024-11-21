<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign('carts_payment_type_id_foreign');
            $table->dropForeign('carts_price_list_id_foreign');
        });
    }
};
