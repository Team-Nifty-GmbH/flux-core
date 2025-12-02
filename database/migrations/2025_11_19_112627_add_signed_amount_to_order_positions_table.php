<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->decimal('signed_amount', 40, 10)->nullable()->after('amount');
        });

        $this->migrateSignedAmount();
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('signed_amount');
        });
    }

    private function migrateSignedAmount(): void
    {
        DB::table('order_positions')
            ->join('orders', 'order_positions.order_id', '=', 'orders.id')
            ->join('order_types', 'orders.order_type_id', '=', 'order_types.id')
            ->whereIn('order_types.order_type_enum', ['retoure', 'purchase', 'refund', 'purchase-subscription'])
            ->update(['signed_amount' => DB::raw('amount * -1')]);

        DB::table('order_positions')
            ->whereNull('signed_amount')
            ->update(['signed_amount' => DB::raw('amount')]);
    }
};
