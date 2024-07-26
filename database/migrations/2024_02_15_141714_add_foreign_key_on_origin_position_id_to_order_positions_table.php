<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // remove the origin if the relation doesnt exist
        DB::table('order_positions as op1')
            ->leftJoin('order_positions as op2', 'op1.origin_position_id', '=', 'op2.id')
            ->whereNull('op2.id')
            ->whereNotNull('op1.origin_position_id')
            ->update(['op1.origin_position_id' => null]);

        Schema::table('order_positions', function (Blueprint $table) {
            $table->foreign('origin_position_id')->references('id')->on('order_positions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_origin_position_id_foreign');
        });
    }
};
