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
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->decimal('count', 40, 10, true)->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->unsignedInteger('count')->default(1)->change();
        });
    }
};
