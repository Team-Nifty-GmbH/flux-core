<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table) {
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['payment_run_id'])->references(['id'])->on('payment_runs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table) {
            $table->dropForeign('order_payment_run_order_id_foreign');
            $table->dropForeign('order_payment_run_payment_run_id_foreign');
        });
    }
};
