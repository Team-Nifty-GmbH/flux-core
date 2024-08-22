<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_purchase_price', 40, 10)
                ->default(0)
                ->after('total_base_gross_price');
            $table->decimal('gross_profit', 40, 10)
                ->default(0)
                ->after('total_base_gross_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_purchase_price', 'gross_profit']);
        });
    }
};
