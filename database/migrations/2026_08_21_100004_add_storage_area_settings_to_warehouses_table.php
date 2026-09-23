<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->string('stock_removal_strategy_enum')
                ->default('fifo')
                ->after('name');
            $table->boolean('requires_storage_area')
                ->default(false)
                ->after('is_default')
                ->comment('When enabled, stock movements in this warehouse must name a storage area.');
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropColumn(['requires_storage_area', 'stock_removal_strategy_enum']);
        });
    }
};
