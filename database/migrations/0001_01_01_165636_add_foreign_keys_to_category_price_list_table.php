<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_price_list', function (Blueprint $table) {
            $table->foreign(['category_id'])->references(['id'])->on('categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['discount_id'])->references(['id'])->on('discounts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('category_price_list', function (Blueprint $table) {
            $table->dropForeign('category_price_list_category_id_foreign');
            $table->dropForeign('category_price_list_discount_id_foreign');
            $table->dropForeign('category_price_list_price_list_id_foreign');
        });
    }
};
