<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            // IDS
            $table->id();
            $table->uuid('uuid');

            // FOREIGNS
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('vat_rate_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('purchase_unit_id')->nullable();
            $table->unsignedBigInteger('reference_unit_id')->nullable();

            // TEXT STRING INT
            $table->text('product_number')->nullable();
            $table->text('name')->nullable();
            $table->text('description')->nullable();
            $table->text('ean')->nullable();
            $table->integer('min_delivery_time')->nullable();
            $table->integer('max_delivery_time')->nullable();
            $table->integer('restock_time')->nullable();
            $table->float('purchase_steps')->nullable();
            $table->float('min_purchase')->nullable();
            $table->float('max_purchase')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('locked_by_user_id')->nullable();
            $table->text('manufacturer_product_number')->nullable();
            $table->text('posting_account')->nullable();
            $table->float('warning_stock_amount')->nullable();

            // BOOLEANS
            $table->integer('option_bundle_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shipping_free')->default(false);
            $table->boolean('is_valid_bundle_status')->default(false);
            $table->boolean('is_required_product_serial_number')->default(false);
            $table->boolean('is_required_manufacturer_serial_number')->default(false);
            $table->boolean('is_auto_create_serial_number')->default(false);
            $table->boolean('is_product_serial_number')->default(false);
            $table->boolean('is_active_always_export_in_web_shop')->default(false);
            $table->boolean('is_active_export_to_web_shop')->default(false);
            $table->boolean('is_force_active')->default(false);
            $table->boolean('is_force_inactive')->default(false);

            // TIMESTAMPS
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->timestamp('deleted_at')->nullable()
                ->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.');

            // DB FOREIGNS
            $table->foreign('parent_id')->references('id')->on('products');
            $table->foreign('vat_rate_id')->references('id')->on('vat_rates');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('purchase_unit_id')->references('id')->on('units');
            $table->foreign('reference_unit_id')->references('id')->on('units');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
