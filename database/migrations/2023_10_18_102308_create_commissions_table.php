<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('commission_rate_id')->nullable();
            $table->foreignId('credit_note_order_position_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_position_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->json('commission_rate');
            $table->decimal('total_net_price', 40, 10);
            $table->decimal('commission', 40, 10);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('commission_rate_id')
                ->references('id')
                ->on('commission_rates')
                ->nullOnDelete();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();
            $table->foreign('order_position_id')
                ->references('id')
                ->on('order_positions')
                ->nullOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
