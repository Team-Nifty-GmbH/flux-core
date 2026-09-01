<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->date('system_delivery_date')
                ->nullable()
                ->after('possible_delivery_date');
            $table->date('system_delivery_date_end')
                ->nullable()
                ->after('system_delivery_date');

            $table->index('system_delivery_date');
            $table->index('system_delivery_date_end');
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropIndex(['system_delivery_date_end']);
            $table->dropIndex(['system_delivery_date']);
            $table->dropColumn(['system_delivery_date', 'system_delivery_date_end']);
        });
    }
};
