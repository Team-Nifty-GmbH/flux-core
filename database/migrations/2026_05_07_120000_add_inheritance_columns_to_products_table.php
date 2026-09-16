<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('overridden_fields')->nullable()->after('warning_stock_amount');
            $table->boolean('is_variant_parent')->default(false)->after('is_shipping_free')->index();
        });

        DB::statement('
            UPDATE products
            INNER JOIN (
                SELECT DISTINCT parent_id AS id
                FROM products
                WHERE parent_id IS NOT NULL
            ) AS parents ON products.id = parents.id
            SET products.is_variant_parent = 1
        ');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_variant_parent']);
            $table->dropColumn(['is_variant_parent', 'overridden_fields']);
        });
    }
};
