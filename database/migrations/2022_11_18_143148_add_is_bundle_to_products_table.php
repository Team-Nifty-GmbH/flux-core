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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_bundle')->default(false)->after('is_active');

            $table->dropColumn(['is_pre_packed', 'is_valid_bundle_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_bundle');

            $table->unsignedInteger('is_pre_packed')->nullable()->after('warning_stock_amount');
            $table->boolean('is_valid_bundle_status')->default(false)->after('is_shipping_free');
        });
    }
};
