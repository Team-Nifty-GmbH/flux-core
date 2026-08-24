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

        // Every purchase subscription so far produced its own document, because that
        // is what the scheduler did unconditionally. Keeping them on that setting
        // leaves rent, insurance and broadcasting fees exactly as they are.
        DB::table('orders')
            ->whereIn(
                'order_type_id',
                DB::table('order_types')
                    ->where('order_type_enum', 'purchase-subscription')
                    ->select('id')
            )
            ->update(['is_self_billed' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('is_self_billed');
        });
    }
};
