<?php

use FluxErp\Enums\StockRemovalStrategyEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->boolean('requires_bin_location')
                ->default(false)
                ->after('is_default')
                ->comment('When enabled, stock movements in this warehouse must name a bin.');
            $table->string('stock_removal_strategy_enum')
                ->default(StockRemovalStrategyEnum::Fifo->value)
                ->after('requires_bin_location');
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropColumn(['requires_bin_location', 'stock_removal_strategy_enum']);
        });
    }
};
