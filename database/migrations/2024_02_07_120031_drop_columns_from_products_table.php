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
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'manufacturer_product_number',
                'is_required_manufacturer_serial_number',
                'is_auto_create_serial_number',
                'is_product_serial_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->text('manufacturer_product_number')->nullable()->after('seo_keywords');
            $table->boolean('is_required_manufacturer_serial_number')
                ->default(false)
                ->after('is_required_product_serial_number');
            $table->boolean('is_auto_create_serial_number')
                ->default(false)
                ->after('is_required_manufacturer_serial_number');
            $table->boolean('is_product_serial_number')->default(false)->after('is_auto_create_serial_number');
        });
    }
};
