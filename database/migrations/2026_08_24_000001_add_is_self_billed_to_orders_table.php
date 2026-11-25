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
            $table->boolean('is_self_billed')->default(false)->after('is_locked');
        });

        $orderTypeIds = DB::table('order_types')
            ->where('order_type_enum', 'purchase-subscription')
            ->pluck('id')
            ->all();

        if ($orderTypeIds) {
            DB::table('orders')
                ->whereIntegerInRaw('order_type_id', $orderTypeIds)
                ->update(['is_self_billed' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('is_self_billed');
        });
    }
};
