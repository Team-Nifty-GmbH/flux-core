<?php

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        // Orders in open payment runs -> in_open_payment_run
        $openPaymentRunOrderIds = resolve_static(PaymentRun::class, 'query')
            ->where('state', 'open')
            ->with('orders:id')
            ->get()
            ->flatMap(fn (PaymentRun $pr) => $pr->orders->pluck('id'))
            ->unique();

        if ($openPaymentRunOrderIds->isNotEmpty()) {
            resolve_static(Order::class, 'query')
                ->whereIntegerInRaw('id', $openPaymentRunOrderIds)
                ->where('payment_state', 'open')
                ->update(['payment_state' => 'in_open_payment_run']);
        }

        // Orders in executed payment runs (pending/successful) -> in_payment
        $executedPaymentRunOrderIds = resolve_static(PaymentRun::class, 'query')
            ->whereIn('state', ['pending', 'successful'])
            ->with('orders:id')
            ->get()
            ->flatMap(fn (PaymentRun $pr) => $pr->orders->pluck('id'))
            ->unique();

        if ($executedPaymentRunOrderIds->isNotEmpty()) {
            resolve_static(Order::class, 'query')
                ->whereIntegerInRaw('id', $executedPaymentRunOrderIds)
                ->where('payment_state', 'open')
                ->update(['payment_state' => 'in_payment']);
        }
    }

    public function down(): void
    {
        resolve_static(Order::class, 'query')
            ->whereIn('payment_state', ['in_open_payment_run', 'in_payment'])
            ->update(['payment_state' => 'open']);
    }
};
