<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // Orders in open payment runs -> in_open_payment_run
        $openOrderIds = DB::table('order_payment_run')
            ->join('payment_runs', 'order_payment_run.payment_run_id', '=', 'payment_runs.id')
            ->where('payment_runs.state', 'open')
            ->pluck('order_payment_run.order_id')
            ->unique();

        if ($openOrderIds->isNotEmpty()) {
            DB::table('orders')
                ->whereIn('id', $openOrderIds)
                ->where('payment_state', 'open')
                ->update(['payment_state' => 'in_open_payment_run']);
        }

        // Orders in executed payment runs (pending/successful) -> in_payment
        $executedOrderIds = DB::table('order_payment_run')
            ->join('payment_runs', 'order_payment_run.payment_run_id', '=', 'payment_runs.id')
            ->whereIn('payment_runs.state', ['pending', 'successful'])
            ->pluck('order_payment_run.order_id')
            ->unique();

        if ($executedOrderIds->isNotEmpty()) {
            DB::table('orders')
                ->whereIn('id', $executedOrderIds)
                ->where('payment_state', 'open')
                ->update(['payment_state' => 'in_payment']);
        }
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereIn('payment_state', ['in_open_payment_run', 'in_payment'])
            ->update(['payment_state' => 'open']);
    }
};
