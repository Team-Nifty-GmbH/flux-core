<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('address_address_type_order', function (Blueprint $table) {
            $table->foreign(['address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['address_type_id'])->references(['id'])->on('address_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('address_address_type_order', function (Blueprint $table) {
            $table->dropForeign('address_address_type_order_address_id_foreign');
            $table->dropForeign('address_address_type_order_address_type_id_foreign');
            $table->dropForeign('address_address_type_order_order_id_foreign');
        });
    }
};
