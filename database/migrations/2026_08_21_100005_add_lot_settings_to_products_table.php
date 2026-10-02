<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('stock_removal_strategy_enum')
                ->nullable()
                ->after('time_unit_enum')
                ->comment('Overrides the strategy of the warehouse; null falls back to it.');
            $table->unsignedInteger('min_shelf_life_days')
                ->nullable()
                ->after('restock_time')
                ->comment('Warn when the remaining shelf life of a lot drops below this many days.');
            $table->boolean('is_lot_tracked')
                ->default(false)
                ->after('is_highlight')
                ->comment('When enabled, every stock receipt of this product must name a lot.');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['is_lot_tracked', 'stock_removal_strategy_enum', 'min_shelf_life_days']);
        });
    }
};
