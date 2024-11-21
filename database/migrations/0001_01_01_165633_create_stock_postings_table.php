<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_postings')) {
            return;
        }

        Schema::create('stock_postings', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('warehouse_id')->index('stock_postings_warehouse_id_foreign');
            $table->unsignedBigInteger('product_id')->index('stock_postings_product_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('stock_postings_parent_id_foreign');
            $table->unsignedBigInteger('order_position_id')->nullable()->index('stock_postings_order_position_id_foreign');
            $table->unsignedBigInteger('serial_number_id')->nullable()->index('stock_postings_serial_number_id_foreign');
            $table->decimal('stock', 40, 10)->nullable();
            $table->decimal('remaining_stock', 40, 10)->nullable();
            $table->decimal('reserved_stock', 40, 10)->nullable();
            $table->decimal('posting', 40, 10);
            $table->decimal('purchase_price', 40, 10)->nullable()->comment('The full price paid for the entirety of this stock posting.');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_postings');
    }
};
