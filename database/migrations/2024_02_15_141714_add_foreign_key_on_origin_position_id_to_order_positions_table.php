<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
