<?php

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRunPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillPaymentRunPositions extends Migration
{
    public function up(): void
    {
        $this->backfill();
    }

    public function down(): void
    {
        DB::table('order_payment_run')->update(['payment_run_position_id' => null]);
        DB::table('payment_run_positions')->delete();
    }

    public function backfill(int $chunkSize = 1000): void
    {
        DB::table('order_payment_run')
            ->whereNull('payment_run_position_id')
            ->lazyById($chunkSize, 'pivot_id')
            ->each(function (object $pivot): void {
                $order = resolve_static(Order::class, 'query')
                    ->with('contactBankConnection:id,iban,bic,account_holder')
                    ->whereKey($pivot->order_id)
                    ->first();

                $position = app(PaymentRunPosition::class, [
                    'attributes' => [
                        'payment_run_id' => $pivot->payment_run_id,
                        'contact_id' => $order?->contact_id,
                        'iban' => $order?->iban ?: $order?->contactBankConnection?->iban,
                        'bic' => $order?->bic ?: $order?->contactBankConnection?->bic,
                        'account_holder' => $order?->account_holder
                            ?: $order?->contactBankConnection?->account_holder,
                        'amount' => $pivot->amount,
                        'purpose' => $order?->invoice_number,
                        'end_to_end_id' => $order?->invoice_number ?: Str::uuid()->toString(),
                    ],
                ]);
                $position->save();

                DB::table('order_payment_run')
                    ->where('pivot_id', $pivot->pivot_id)
                    ->update(['payment_run_position_id' => $position->getKey()]);
            });
    }
}

return new BackfillPaymentRunPositions();
