<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('cover_media_id')->nullable()->index('products_cover_media_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('products_parent_id_foreign');
            $table->unsignedBigInteger('vat_rate_id')->nullable()->index('products_vat_rate_id_foreign');
            $table->unsignedBigInteger('unit_id')->nullable()->index('products_unit_id_foreign');
            $table->unsignedBigInteger('purchase_unit_id')->nullable()->index('products_purchase_unit_id_foreign');
            $table->unsignedBigInteger('reference_unit_id')->nullable()->index('products_reference_unit_id_foreign');
            $table->text('product_number')->nullable();
            $table->string('product_type')->nullable();
            $table->string('name')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('weight_gram', 40, 10)->nullable();
            $table->decimal('dimension_length_mm', 40, 10)->nullable();
            $table->decimal('dimension_width_mm', 40, 10)->nullable();
            $table->decimal('dimension_height_mm', 40, 10)->nullable();
            $table->decimal('selling_unit', 40, 10)->nullable();
            $table->decimal('basic_unit', 40, 10)->nullable();
            $table->string('time_unit_enum')->nullable();
            $table->text('ean')->nullable();
            $table->integer('min_delivery_time')->nullable();
            $table->integer('max_delivery_time')->nullable();
            $table->integer('restock_time')->nullable();
            $table->double('purchase_steps')->nullable();
            $table->double('min_purchase')->nullable();
            $table->double('max_purchase')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('posting_account')->nullable();
            $table->double('warning_stock_amount')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_highlight')->default(false);
            $table->boolean('is_bundle')->default(false);
            $table->boolean('is_service')->default(false);
            $table->boolean('is_shipping_free')->default(false);
            $table->boolean('has_serial_numbers')->default(false);
            $table->boolean('is_nos')->default(false);
            $table->boolean('is_active_export_to_web_shop')->default(false);
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
        Schema::dropIfExists('products');
    }
};
