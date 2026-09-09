<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->boolean('is_inherited')->default(false)->after('price')->index();
        });

        Schema::table('categorizable', function (Blueprint $table): void {
            $table->boolean('is_inherited')->default(false)->after('categorizable_id')->index();
        });

        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->boolean('is_inherited')->default(false)->after('purchase_price')->index();
        });

        Schema::table('product_product_property', function (Blueprint $table): void {
            $table->boolean('is_inherited')->default(false)->after('value')->index();
        });
    }

    public function down(): void
    {
        foreach (['prices', 'categorizable', 'product_supplier', 'product_product_property'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex([$tableName . '_is_inherited_index']);
                $table->dropColumn('is_inherited');
            });
        }
    }
};
