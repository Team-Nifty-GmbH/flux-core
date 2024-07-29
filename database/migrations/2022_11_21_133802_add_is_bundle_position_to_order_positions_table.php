<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->boolean('is_bundle_position')->default(false)->after('is_no_product');
            $table->renameColumn('is_no_product', 'is_free_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropColumn('is_bundle_position');
            $table->renameColumn('is_free_text', 'is_no_product');
        });
    }
};
