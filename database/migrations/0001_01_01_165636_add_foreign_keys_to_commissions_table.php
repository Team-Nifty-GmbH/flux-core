<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreign(['commission_rate_id'])->references(['id'])->on('commission_rates')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['credit_note_order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign('commissions_commission_rate_id_foreign');
            $table->dropForeign('commissions_credit_note_order_position_id_foreign');
            $table->dropForeign('commissions_order_id_foreign');
            $table->dropForeign('commissions_order_position_id_foreign');
            $table->dropForeign('commissions_user_id_foreign');
        });
    }
};
