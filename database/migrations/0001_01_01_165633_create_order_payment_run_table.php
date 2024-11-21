<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_payment_run')) {
            return;
        }

        Schema::create('order_payment_run', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index('order_payment_run_order_id_foreign');
            $table->unsignedBigInteger('payment_run_id')->index('order_payment_run_payment_run_id_foreign');
            $table->decimal('amount', 40, 10);
            $table->boolean('success')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_run');
    }
};
