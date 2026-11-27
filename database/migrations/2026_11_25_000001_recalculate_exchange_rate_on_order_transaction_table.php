<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('order_transaction')
            ->whereNotNull('order_currency_amount')
            ->where('amount', '<>', 0)
            ->update([
                'exchange_rate' => DB::raw('TRUNCATE(order_currency_amount / amount, 10)'),
            ]);
    }

    public function down(): void {}
};
