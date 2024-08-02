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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('commission')->nullable()->after('payment_reminder_days_3');
            $table->string('order_number')->unique()->after('payment_reminder_days_3');
            $table->dropColumn(
                [
                    'vats',
                    'sum_order_position_net',
                    'sum_order_position_gross',
                    'total_price_gross',
                    'total_price_net',
                ]
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['commission', 'order_number']);
            $table->json('vats')->after('tracking_email');
            $table->decimal('sum_order_position_net', 40, 10)->default(0)
                ->after('payment_discount_percent');
            $table->decimal('sum_order_position_gross', 40, 10)->default(0)
                ->after('sum_order_position_net');
            $table->decimal('total_price_gross', 40, 10)->default(0)
                ->after('sum_order_position_net');
            $table->decimal('total_price_net', 40, 10)->default(0)
                ->after('total_price_gross');
        });
    }
};
