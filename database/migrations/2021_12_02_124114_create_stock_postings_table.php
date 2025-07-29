<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('stock_postings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('order_position_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('stock_postings')
                ->nullOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreignId('serial_number_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->unsignedBigInteger('warehouse_id');

            $table->decimal('stock', 40, 10)->nullable();
            $table->decimal('remaining_stock', 40, 10)->nullable();
            $table->decimal('reserved_stock', 40, 10)->nullable();
            $table->decimal('posting', 40, 10);
            $table->decimal('purchase_price', 40, 10)->nullable()
                ->comment('The full price paid for the entirety of this stock posting.');
            $table->text('description')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_postings');
    }
};
