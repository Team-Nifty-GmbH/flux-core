<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table) {
            $table->json('post_stock_print_layouts')->nullable()->after('print_layouts');
            $table->json('reserve_stock_print_layouts')->nullable()->after('post_stock_print_layouts');
        });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropColumn([
                'post_stock_print_layouts',
                'reserve_stock_print_layouts',
            ]);
        });
    }
};
