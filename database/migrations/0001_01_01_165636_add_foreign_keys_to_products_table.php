<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['cover_media_id'])->references(['id'])->on('media')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['purchase_unit_id'])->references(['id'])->on('units')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['reference_unit_id'])->references(['id'])->on('units')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['unit_id'])->references(['id'])->on('units')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['vat_rate_id'])->references(['id'])->on('vat_rates')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_cover_media_id_foreign');
            $table->dropForeign('products_parent_id_foreign');
            $table->dropForeign('products_purchase_unit_id_foreign');
            $table->dropForeign('products_reference_unit_id_foreign');
            $table->dropForeign('products_unit_id_foreign');
            $table->dropForeign('products_vat_rate_id_foreign');
        });
    }
};
