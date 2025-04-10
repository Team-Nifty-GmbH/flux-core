<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->boolean('is_ignored')->default(false)->after('counterpart_bank_name');
            $table->decimal('balance', 40, 10)->nullable()->after('amount');
        });

        $this->migrateBalance();
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropColumn([
                'is_ignored',
                'balance',
            ]);
        });
    }

    private function migrateBalance(): void
    {
        DB::transaction(function (): void {
            DB::table('transactions')
                ->update([
                    'balance' => DB::raw(
                        'transactions.amount - COALESCE(
                            (SELECT SUM(amount)
                            FROM order_transaction
                            WHERE order_transaction.is_accepted = 1
                            AND order_transaction.transaction_id = transactions.id),
                        0)'),
                ]);
        });
    }
};
