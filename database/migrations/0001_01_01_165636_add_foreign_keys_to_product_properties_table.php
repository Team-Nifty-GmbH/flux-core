<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_properties', function (Blueprint $table) {
            $table->foreign(['product_property_group_id'])->references(['id'])->on('product_property_groups')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropForeign('product_properties_product_property_group_id_foreign');
        });
    }
};
