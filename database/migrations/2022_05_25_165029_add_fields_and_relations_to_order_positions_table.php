<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAndRelationsToOrderPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_positions', function (Blueprint $table) {
            // Foreign Keys
            $table->unsignedBigInteger('client_id')->after('uuid')
                ->comment('A unique identifier number for the table clients.');
            $table->unsignedBigInteger('parent_id')->nullable()->after('order_id')
                ->comment('A unique identifier number for the table order_positions. This connects this position to one parent-position.');
            $table->unsignedBigInteger('price_id')->nullable()->after('parent_id')
                ->comment('A unique identifier number for the table prices.');
            $table->unsignedBigInteger('price_list_id')->nullable()->after('price_id')
                ->comment('A unique identifier number for the table price_lists.');
            $table->unsignedBigInteger('product_id')->nullable()->after('price_list_id')
                ->comment('A unique identifier number for the table products.');
            $table->unsignedBigInteger('supplier_contact_id')->nullable()->after('product_id')
                ->comment('A unique identifier number for the table contacts.');
            $table->unsignedBigInteger('vat_rate_id')->nullable()->after('supplier_contact_id')
                ->comment('A unique identifier number for the table vat_rates.');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('vat_rate_id')
                ->comment('A unique identifier number for the table warehouses.');

            // Price-Relevant Fields
            $table->decimal('amount', 40, 10)
                ->nullable()
                ->after('warehouse_id')
                ->comment('A decimal containing the accurate amount this order-position uses of its associated product.');
            $table->decimal('discount_percentage')
                ->nullable()
                ->after('amount')
                ->comment('A number containing the cached full discount in percent for this order-position.');
            $table->decimal('margin', 40, 10)
                ->nullable()
                ->after('discount_percentage')
                ->comment('A decimal containing the cached margin calculated for this order-position.');
            $table->decimal('provision', 40, 10)
                ->nullable()
                ->after('margin')
                ->comment('A decimal containing the provision for this order-position.');
            $table->decimal('purchase_price', 40, 10)
                ->nullable()
                ->after('provision')
                ->comment('A decimal containing the purchase price per unit of the associated product. This gets calculated from stock-postings.');
            $table->decimal('total_price', 40, 10)
                ->nullable()
                ->after('purchase_price')
                ->comment('A decimal containing the order-position total price after all calculations. Can be net or gross depending on the field is_net.');
            $table->decimal('unit_price', 40, 10)
                ->nullable()
                ->after('total_price')
                ->comment('A decimal containing the price per unit of the associated product.');
            $table->decimal('vat_rate', 40, 10)
                ->nullable()
                ->after('unit_price')
                ->comment('A decimal, containing the vat-rate in percent, that is cached for easier and faster readability of this order-position.');

            // Misc Fields
            $table->decimal('amount_packed_products', 40, 10)
                ->nullable()
                ->after('vat_rate')
                ->comment('A decimal containing the amount of packed products for this order-position.');
            $table->date('customer_delivery_date')
                ->nullable()
                ->after('amount_packed_products')
                ->comment('A date containing the delivery date desired by the customer for this order-position.');
            $table->string('ean_code')
                ->nullable()
                ->after('customer_delivery_date')
                ->comment('A number containing the European Article Number for the associated product for this order-position.');
            $table->date('possible_delivery_date')
                ->nullable()
                ->after('ean_code')
                ->comment('A date containing the earliest possible delivery date for this order-position.');
            $table->decimal('unit_gram_weight', 40, 10)
                ->nullable()
                ->after('possible_delivery_date')
                ->comment('A decimal containing the weight per unit for the associated product for this order-position in grams.');

            // Descriptive Fields
            $table->text('description')
                ->nullable()
                ->after('unit_gram_weight')
                ->comment('A string containing a description for the current order-position. Normally this text contains the associated product-description');
            $table->text('name')
                ->after('description')
                ->comment('A string containing a descriptive name or text for the current order-position. Normally this text is set to the associated product-name');
            $table->string('product_number')
                ->nullable()
                ->after('name')
                ->comment('A string containing the associated product-number.');
            $table->unsignedInteger('sort_number')
                ->after('product_number')
                ->comment('A number to determine the order-positions position in the order.');

            $table->boolean('is_net')
                ->default(false)
                ->after('sort_number')
                ->comment('A boolean deciding if this order-position is calculated in net instead of gross.');
            $table->boolean('is_no_product')
                ->default(false)
                ->after('is_net')
                ->comment('A boolean deciding if this order-position is just free text instead of having a product associated.');
            $table->boolean('is_positive_operator')
                ->default(true)
                ->after('is_no_product')
                ->comment('A boolean defining if this order-position has a positive or negative total.');

            // Foreign Keys
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('parent_id')->references('id')->on('order_positions');
            $table->foreign('price_id')->references('id')->on('prices');
            $table->foreign('price_list_id')->references('id')->on('prices');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('supplier_contact_id')->references('id')->on('contacts');
            $table->foreign('vat_rate_id')->references('id')->on('vat_rates');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->dropColumn(['total']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_client_id_foreign');
            $table->dropForeign('order_positions_order_id_foreign');
            $table->dropForeign('order_positions_parent_id_foreign');
            $table->dropForeign('order_positions_price_id_foreign');
            $table->dropForeign('order_positions_price_list_id_foreign');
            $table->dropForeign('order_positions_product_id_foreign');
            $table->dropForeign('order_positions_supplier_contact_id_foreign');
            $table->dropForeign('order_positions_vat_rate_id_foreign');
            $table->dropForeign('order_positions_warehouse_id_foreign');

            $table->dropColumn([
                'client_id',
                'parent_id',
                'price_id',
                'product_id',
                'supplier_contact_id',
                'vat_rate_id',
                'warehouse_id',

                'amount',
                'discount_percentage',
                'margin',
                'provision',
                'purchase_price',
                'total_price',
                'unit_price',
                'vat_rate',

                'amount_packed_products',
                'customer_delivery_date',
                'ean_code',
                'possible_delivery_date',
                'unit_gram_weight',

                'description',
                'name',
                'product_number',
                'sort_number',

                'is_net',
                'is_no_product',
                'is_positive_operator',
            ]);

            $table->double('total');
        });
    }
}
