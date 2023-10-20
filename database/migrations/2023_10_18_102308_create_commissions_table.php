<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('commission_rate_id')->nullable();
            $table->unsignedBigInteger('order_position_id')->nullable();
            $table->decimal('commission', 40, 10);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('commission_rate_id')->references('id')->on('commission_rates')->nullOnDelete();
            $table->foreign('order_position_id')->references('id')->on('order_positions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
