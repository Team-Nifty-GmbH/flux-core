<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_transaction', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 40, 10);
            $table->boolean('is_accepted')->default(false);
            $table->timestamps();
        });

        $this->migrateTransactions();

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::transaction(function (): void {
            DB::table('transactions')
                ->join('order_transaction', 'transactions.id', '=', 'order_transaction.transaction_id')
                ->update(['transactions.order_id' => DB::raw('order_transaction.order_id')]);
        });

        Schema::dropIfExists('order_transaction');
    }

    private function migrateTransactions(): void
    {
        DB::statement('
            INSERT INTO order_transaction (transaction_id, order_id, amount, is_accepted, created_at, updated_at)
            SELECT id, order_id, amount, true, updated_at, updated_at
            FROM transactions
            WHERE order_id IS NOT NULL'
        );
    }
};
