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

        Schema::create('order_positions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->foreignId('client_id')
                ->constrained('clients');
            $table->foreignId('created_from_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
            $table->foreignId('credit_account_id')
                ->nullable()
                ->constrained('contact_bank_connections')
                ->nullOnDelete();
            $table->foreignId('ledger_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('origin_position_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('order_positions')
                ->cascadeOnDelete();
            $table->foreignId('price_list_id')
                ->nullable()
                ->constrained('price_lists')
                ->nullOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->foreignId('supplier_contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('vat_rate_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->decimal('amount', 40, 10)
                ->nullable()
                ->comment('A decimal containing the accurate amount this order-position uses of its associated product.');
            $table->decimal('amount_bundle', 40, 10)->nullable();
            $table->decimal('discount_percentage', 11, 10)
                ->nullable()
                ->comment('A number containing the cached full discount in percent for this order-position.');
            $table->decimal('margin', 40, 10)
                ->nullable()
                ->comment('A decimal containing the cached margin calculated for this order-position.');
            $table->decimal('provision', 40, 10)
                ->nullable()
                ->comment('A decimal containing the provision for this order-position.');
            $table->decimal('purchase_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the purchase price per unit of the associated product. This gets calculated from stock-postings.');
            $table->decimal('total_base_gross_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the order-position total price before any discounts.' .
                    ' Can be net or gross depending on the field is_net.');

            $table->decimal('total_base_net_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the order-position total price before any discounts.' .
                    ' Can be net or gross depending on the field is_net.');

            $table->decimal('total_gross_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the order-position total price gross after all calculations.');
            $table->decimal('total_net_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the order-position total price gross after all calculations.');
            $table->decimal('vat_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the tax for this order-position.');
            $table->decimal('unit_net_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the price per unit of the associated product.');
            $table->decimal('unit_gross_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the price per unit net of the associated product.');
            $table->decimal('vat_rate_percentage', 40, 10)
                ->nullable()
                ->comment('A decimal, containing the vat-rate in percent, that is cached for easier and faster readability of this order-position.');
            $table->decimal('amount_packed_products', 40, 10)
                ->nullable()
                ->comment('A decimal containing the amount of packed products for this order-position.');
            $table->date('customer_delivery_date')
                ->nullable()
                ->comment('A date containing the delivery date desired by the customer for this order-position.');
            $table->string('ean_code')
                ->nullable()
                ->comment('A number containing the European Article Number for the associated product for this order-position.');
            $table->date('possible_delivery_date')
                ->nullable()
                ->comment('A date containing the earliest possible delivery date for this order-position.');
            $table->decimal('unit_gram_weight', 40, 10)
                ->nullable()
                ->comment('A decimal containing the weight per unit for the associated product for this order-position in grams.');
            $table->text('description')
                ->nullable()
                ->comment('A string containing a description for the current order-position. Normally this text contains the associated product-description');
            $table->text('name')
                ->comment('A string containing a descriptive name or text for the current order-position. Normally this text is set to the associated product-name');
            $table->string('product_number')
                ->nullable()
                ->comment('A string containing the associated product-number.');
            $table->json('product_prices')->nullable();
            $table->decimal('credit_amount', 40, 10)->nullable();
            $table->tinyInteger('post_on_credit_account')->nullable()
                ->comment('> 0: credit, < 0: debit \'credit_amount\' on the given credit account.'
                    . ' 0 or null: no transaction.'
                );
            $table->unsignedInteger('sort_number')
                ->comment('A number to determine the order-positions position in the order.');
            $table->string('slug_position')->nullable();

            $table->boolean('is_alternative')->default(false);
            $table->boolean('is_bundle_position')->default(false);
            $table->boolean('is_free_text')
                ->default(false)
                ->comment('A boolean deciding if this order-position is just free text instead of having a product associated.');
            $table->boolean('is_net')
                ->default(false)
                ->comment('A boolean deciding if this order-position is calculated in net instead of gross.');

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
        Schema::dropIfExists('order_positions');
    }
};
