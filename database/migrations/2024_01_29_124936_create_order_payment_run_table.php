<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_payment_run', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('payment_run_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('amount', 40, 10);
            $table->boolean('success')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_run');
    }
};
