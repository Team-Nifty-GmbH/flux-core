<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
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
                $order = DB::table('orders')
                    ->leftJoin('contact_bank_connections', function (JoinClause $join): void {
                        $join->on('contact_bank_connections.id', '=', 'orders.contact_bank_connection_id')
                            ->whereNull('contact_bank_connections.deleted_at');
                    })
                    ->where('orders.id', $pivot->order_id)
                    ->first([
                        'orders.contact_id',
                        'orders.invoice_number',
                        'orders.iban',
                        'orders.bic',
                        'orders.account_holder',
                        'contact_bank_connections.iban AS bank_iban',
                        'contact_bank_connections.bic AS bank_bic',
                        'contact_bank_connections.account_holder AS bank_account_holder',
                    ]);

                $positionId = DB::table('payment_run_positions')->insertGetId([
                    'uuid' => Str::uuid()->toString(),
                    'payment_run_id' => $pivot->payment_run_id,
                    'contact_id' => $order?->contact_id,
                    'iban' => $order?->iban ?: $order?->bank_iban,
                    'bic' => $order?->bic ?: $order?->bank_bic,
                    'account_holder' => $order?->account_holder ?: $order?->bank_account_holder,
                    'amount' => $pivot->amount,
                    'purpose' => $order?->invoice_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('payment_run_positions')
                    ->where('id', $positionId)
                    ->update(['end_to_end_id' => 'PR' . $pivot->payment_run_id . '-' . $positionId]);

                DB::table('order_payment_run')
                    ->where('pivot_id', $pivot->pivot_id)
                    ->update(['payment_run_position_id' => $positionId]);
            });
    }
};
