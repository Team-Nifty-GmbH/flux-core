<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_positions')) {
            return;
        }

        Schema::create('order_positions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('order_positions_client_id_foreign')->comment('A unique identifier number for the table clients.');
            $table->unsignedBigInteger('ledger_account_id')->nullable()->index('order_positions_ledger_account_id_foreign');
            $table->unsignedBigInteger('order_id')->index('order_positions_order_id_foreign');
            $table->unsignedBigInteger('origin_position_id')->nullable()->index('order_positions_origin_position_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('order_positions_parent_id_foreign')->comment('A unique identifier number for the table order_positions. This connects this position to one parent-position.');
            $table->unsignedBigInteger('price_id')->nullable()->comment('A unique identifier number for the table prices.');
            $table->unsignedBigInteger('price_list_id')->nullable()->index('order_positions_price_list_id_foreign')->comment('A unique identifier number for the table price_lists.');
            $table->unsignedBigInteger('product_id')->nullable()->index('order_positions_product_id_foreign')->comment('A unique identifier number for the table products.');
            $table->unsignedBigInteger('supplier_contact_id')->nullable()->index('order_positions_supplier_contact_id_foreign')->comment('A unique identifier number for the table contacts.');
            $table->unsignedBigInteger('vat_rate_id')->nullable()->index('order_positions_vat_rate_id_foreign')->comment('A unique identifier number for the table vat_rates.');
            $table->unsignedBigInteger('warehouse_id')->nullable()->index('order_positions_warehouse_id_foreign')->comment('A unique identifier number for the table warehouses.');
            $table->decimal('amount', 40, 10)->nullable()->comment('A decimal containing the accurate amount this order-position uses of its associated product.');
            $table->decimal('amount_bundle', 40, 10)->nullable();
            $table->decimal('discount_percentage', 11, 10)->nullable();
            $table->decimal('margin', 40, 10)->nullable()->comment('A decimal containing the cached margin calculated for this order-position.');
            $table->decimal('provision', 40, 10)->nullable()->comment('A decimal containing the provision for this order-position.');
            $table->decimal('purchase_price', 40, 10)->nullable()->comment('A decimal containing the purchase price per unit of the associated product. This gets calculated from stock-postings.');
            $table->decimal('total_base_gross_price', 40, 10)->nullable()->comment('A decimal containing the order-position total price before any discounts. Can be net or gross depending on the field is_net.');
            $table->decimal('total_base_net_price', 40, 10)->nullable()->comment('A decimal containing the order-position total price before any discounts. Can be net or gross depending on the field is_net.');
            $table->decimal('total_gross_price', 40, 10)->nullable()->comment('A decimal containing the order-position total price gross after all calculations.');
            $table->decimal('total_net_price', 40, 10)->nullable()->comment('A decimal containing the order-position total price gross after all calculations.');
            $table->decimal('vat_price', 40, 10)->nullable()->comment('A decimal containing the tax for this order-position.');
            $table->decimal('unit_net_price', 40, 10)->nullable()->comment('A decimal containing the price per unit of the associated product.');
            $table->decimal('unit_gross_price', 40, 10)->nullable()->comment('A decimal containing the price per unit net of the associated product.');
            $table->decimal('vat_rate_percentage', 40, 10)->nullable()->comment('A decimal, containing the vat-rate in percent, that is cached for easier and faster readability of this order-position.');
            $table->decimal('amount_packed_products', 40, 10)->nullable()->comment('A decimal containing the amount of packed products for this order-position.');
            $table->date('customer_delivery_date')->nullable()->comment('A date containing the delivery date desired by the customer for this order-position.');
            $table->string('ean_code')->nullable()->comment('A number containing the European Article Number for the associated product for this order-position.');
            $table->date('possible_delivery_date')->nullable()->comment('A date containing the earliest possible delivery date for this order-position.');
            $table->decimal('unit_gram_weight', 40, 10)->nullable()->comment('A decimal containing the weight per unit for the associated product for this order-position in grams.');
            $table->text('description')->nullable()->comment('A string containing a description for the current order-position. Normally this text contains the associated product-description');
            $table->text('name')->comment('A string containing a descriptive name or text for the current order-position. Normally this text is set to the associated product-name');
            $table->string('product_number')->nullable()->comment('A string containing the associated product-number.');
            $table->json('product_prices')->nullable();
            $table->unsignedInteger('sort_number')->comment('A number to determine the order-positions position in the order.');
            $table->string('slug_position')->nullable();
            $table->boolean('is_alternative')->default(false);
            $table->boolean('is_net')->default(false)->comment('A boolean deciding if this order-position is calculated in net instead of gross.');
            $table->boolean('is_free_text')->default(false)->comment('A boolean deciding if this order-position is just free text instead of having a product associated.');
            $table->boolean('is_bundle_position')->default(false);
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
        Schema::dropIfExists('order_positions');
    }
};
