<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_position_stock_posting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_posting_id')->constrained()->cascadeOnDelete();
            $table->decimal('reserved_amount', 40, 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_position_stock_posting');
    }
};
