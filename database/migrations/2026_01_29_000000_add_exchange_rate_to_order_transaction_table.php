<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_transaction', function (Blueprint $table): void {
            $table->decimal('exchange_rate', 40, 10)
                ->nullable()
                ->after('amount');
            $table->decimal('order_currency_amount', 40, 10)
                ->nullable()
                ->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('order_transaction', function (Blueprint $table): void {
            $table->dropColumn([
                'exchange_rate',
                'order_currency_amount',
            ]);
        });
    }
};
