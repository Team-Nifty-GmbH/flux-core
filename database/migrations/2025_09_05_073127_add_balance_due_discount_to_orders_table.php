<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('balance_due_discount', 40, 10)->nullable()->after('balance');
            $table->date('payment_target_date')->nullable()->after('payment_target');
            $table->date('payment_discount_target_date')->nullable()->after('payment_discount_target');
        });

        $this->migrateExistingData();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'balance_due_discount',
                'payment_target_date',
                'payment_discount_target_date',
            ]);
        });
    }

    private function migrateExistingData(): void
    {
        DB::table('orders')
            ->whereNotNull('invoice_date')
            ->whereNotNull('payment_target')
            ->update([
                'payment_target_date' => DB::raw('DATE_ADD(invoice_date, INTERVAL payment_target DAY)'),
            ]);

        DB::table('orders')
            ->whereNotNull('invoice_date')
            ->whereNotNull('payment_discount_target')
            ->whereNotNull('payment_discount_percent')
            ->update([
                'payment_discount_target_date' => DB::raw('DATE_ADD(invoice_date, INTERVAL payment_discount_target DAY)'),
            ]);
    }
};
