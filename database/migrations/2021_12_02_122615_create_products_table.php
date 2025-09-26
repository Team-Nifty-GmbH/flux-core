<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            // FOREIGN KEYS
            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->foreignId('purchase_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->foreignId('reference_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->foreignId('vat_rate_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();

            // TEXT STRING INT
            $table->text('product_number')->nullable();
            $table->string('product_type')->nullable();
            $table->string('bundle_type_enum')->nullable();
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
            $table->string('customs_tariff_number', 64)->nullable();
            $table->integer('min_delivery_time')->nullable();
            $table->integer('max_delivery_time')->nullable();
            $table->integer('restock_time')->nullable();
            $table->float('purchase_steps')->nullable();
            $table->float('min_purchase')->nullable();
            $table->float('max_purchase')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->json('search_aliases')->nullable();
            $table->text('posting_account')->nullable();
            $table->float('warning_stock_amount')->nullable();

            // BOOLEANS
            $table->boolean('has_serial_numbers')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_active_export_to_web_shop')->default(false);
            $table->boolean('is_bundle')->default(false);
            $table->boolean('is_highlight')->default(false);
            $table->boolean('is_nos')->default(false);
            $table->boolean('is_service')->default(false);
            $table->boolean('is_shipping_free')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
