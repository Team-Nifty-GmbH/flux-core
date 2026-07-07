<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        foreach (['prices', 'categorizable', 'product_supplier', 'product_product_property'] as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->boolean('is_inherited')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['prices', 'categorizable', 'product_supplier', 'product_product_property'] as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->dropIndex([$table . '_is_inherited_index']);
                $t->dropColumn('is_inherited');
            });
        }
    }
};
