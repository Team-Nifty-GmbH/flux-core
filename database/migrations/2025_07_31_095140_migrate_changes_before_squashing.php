<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('address_address_type', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->cascadeOnDelete();
        });

        Schema::table('address_address_type_order', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropForeign(['order_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->cascadeOnDelete();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });

        Schema::table('address_product', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['product_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['language_id']);

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->nullOnDelete();
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->nullOnDelete();
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropForeign('accounts_currency_id_foreign');

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->nullOnDelete();
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });

        Schema::table('categorizables', function (Blueprint $table): void {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });

        Schema::table('client_user', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('contact_bank_connections', function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();
        });

        Schema::table('contact_options', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->nullOnDelete();
        });

        Schema::table('country_regions', function (Blueprint $table): void {
            $table->dropForeign(['country_id']);

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->cascadeOnDelete();
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('event_notifications_event_index');
            $table->dropIndex('event_notifications_model_id_index');
            $table->dropIndex('event_notifications_model_type_index');
            $table->dropIndex('event_subscriptions_subscribable_id_subscribable_type_index');

            $table->index('event');
            $table->index(['model_type', 'model_id']);
            $table->index(['subscribable_type', 'subscribable_id']);
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('price_id');

            $table->dropForeign(['price_list_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['supplier_contact_id']);

            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->nullOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
            $table->foreign('supplier_contact_id')
                ->references('id')
                ->on('contacts')
                ->nullOnDelete();
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'delivery_type_id',
                'logistics_id',
                'unit_price_price_list_id',
                'header_discount',
                'number_of_packages',
                'tracking_email',
                'payment_texts',
                'has_logistic_notify_phone_number',
                'has_logistic_notify_number',
                'is_new_customer',
                'is_merge_invoice',
                'is_paid',
            ]);

            $table->dropForeign(['address_delivery_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);

            $table->foreign('address_delivery_id')
                ->references('id')
                ->on('addresses')
                ->nullOnDelete();
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->nullOnDelete();
            $table->foreign('parent_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });

        Schema::table('price_lists', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('price_lists')
                ->nullOnDelete();
        });

        Schema::table('prices', function (Blueprint $table): void {
            $table->dropForeign(['price_list_id']);
            $table->dropForeign(['product_id']);

            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        Schema::table('product_options', function (Blueprint $table): void {
            $table->dropForeign(['product_option_group_id']);

            $table->foreign('product_option_group_id')
                ->references('id')
                ->on('product_option_groups')
                ->cascadeOnDelete();
        });

        Schema::table('product_product_option', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_option_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_option_id')
                ->references('id')
                ->on('product_options')
                ->cascadeOnDelete();
        });

        Schema::table('product_product_property', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_prop_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_prop_id')
                ->references('id')
                ->on('product_properties')
                ->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['purchase_unit_id']);
            $table->dropForeign(['reference_unit_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['vat_rate_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
            $table->foreign('purchase_unit_id')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
            $table->foreign('reference_unit_id')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
            $table->foreign('vat_rate_id')
                ->references('id')
                ->on('vat_rates')
                ->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['contact_id']);
            $table->dropForeign('sepa_mandates_bank_connection_id_foreign');

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections')
                ->nullOnDelete();
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });

        Schema::table('serial_numbers', function (Blueprint $table): void {
            $table->dropForeign(['serial_number_range_id']);

            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges')
                ->nullOnDelete();
        });

        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['warehouse_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::table('ticket_user', function (Blueprint $table): void {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropForeign(['ticket_type_id']);

            $table->foreign('ticket_type_id')
                ->references('id')
                ->on('ticket_types')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);

            $table->dropForeign(['currency_id']);

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->nullOnDelete();
        });

        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->nullOnDelete();
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('address_address_type', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses');
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types');
        });

        Schema::table('address_address_type_order', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropForeign(['order_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses');
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders');
        });

        Schema::table('address_product', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['product_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['language_id']);

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
            $table->foreign('language_id')
                ->references('id')
                ->on('languages');
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropForeign(['currency_id']);

            $table->foreign('currency_id', 'accounts_currency_id_foreign')
                ->references('id')
                ->on('currencies');
        });

        Schema::table('carts', function (Blueprint $table): void {
            $table->dropColumn([
                'created_by',
                'updated_by',
            ]);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories');
        });

        Schema::table('categorizables', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        Schema::table('client_user', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('contact_bank_connections', function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
        });

        Schema::table('contact_options', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['price_list_id']);
        });

        Schema::table('country_regions', function (Blueprint $table): void {
            $table->dropForeign(['country_id']);

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('event_subscriptions_event_index');
            $table->dropIndex('event_subscriptions_model_type_model_id_index');
            $table->dropIndex('event_subscriptions_subscribable_type_subscribable_id_index');

            $table->index('event', 'event_notifications_event_index');
            $table->index('model_id', 'event_notifications_model_id_index');
            $table->index('model_type', 'event_notifications_model_type_index');
            $table->index(['subscribable_id', 'subscribable_type']);
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('media');
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('price_id')->nullable()->after('credit_account_id');

            $table->dropForeign(['price_list_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['supplier_contact_id']);

            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('supplier_contact_id')
                ->references('id')
                ->on('contacts');
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('unit_price_price_list_id')->nullable()->after('price_list_id');
            $table->unsignedBigInteger('delivery_type_id')->nullable()->after('unit_price_price_list_id');
            $table->unsignedBigInteger('logistics_id')->nullable()->after('delivery_type_id');

            $table->decimal('header_discount', 40, 10)
                ->default(0)
                ->after('payment_discount_percent');
            $table->unsignedInteger('number_of_packages')->nullable()->after('balance');
            $table->string('tracking_email')->nullable()->after('logistic_note');
            $table->json('payment_texts')->nullable()->after('tracking_email');

            $table->boolean('has_logistic_notify_phone_number')
                ->default(false)
                ->after('date_of_approval');
            $table->boolean('has_logistic_notify_number')
                ->default(false)
                ->after('has_logistic_notify_phone_number');
            $table->boolean('is_new_customer')
                ->default(false)
                ->after('is_locked');
            $table->boolean('is_merge_invoice')
                ->default(false)
                ->after('is_new_customer');
            $table->boolean('is_paid')
                ->default(false)
                ->after('is_confirmed');

            $table->dropForeign(['address_delivery_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);

            $table->foreign('address_delivery_id')
                ->references('id')
                ->on('addresses');
            $table->foreign('language_id')
                ->references('id')
                ->on('languages');
            $table->foreign('parent_id')
                ->references('id')
                ->on('orders');
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);

            $table->foreign('order_id')
                ->references('id')
                ->on('orders');
        });

        Schema::table('price_lists', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('price_lists');
        });

        Schema::table('prices', function (Blueprint $table): void {
            $table->dropForeign(['price_list_id']);
            $table->dropForeign(['product_id']);

            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });

        Schema::table('product_options', function (Blueprint $table): void {
            $table->dropForeign(['product_option_group_id']);

            $table->foreign('product_option_group_id')
                ->references('id')
                ->on('product_option_groups');
        });

        Schema::table('product_product_option', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_option_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('product_option_id')
                ->references('id')
                ->on('product_options');
        });

        Schema::table('product_product_property', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_prop_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('product_prop_id')
                ->references('id')
                ->on('product_properties');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['purchase_unit_id']);
            $table->dropForeign(['reference_unit_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['vat_rate_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('products');
            $table->foreign('purchase_unit_id')
                ->references('id')
                ->on('units');
            $table->foreign('reference_unit_id')
                ->references('id')
                ->on('units');
            $table->foreign('unit_id')
                ->references('id')
                ->on('units');
            $table->foreign('vat_rate_id')
                ->references('id')
                ->on('vat_rates');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('projects');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['contact_bank_connection_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->foreign('contact_bank_connection_id', 'sepa_mandates_bank_connection_id_foreign')
                ->references('id')
                ->on('contact_bank_connections');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
        });

        Schema::table('serial_numbers', function (Blueprint $table): void {
            $table->dropForeign(['serial_number_range_id']);

            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges');
        });

        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['warehouse_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses');
        });

        Schema::table('ticket_user', function (Blueprint $table): void {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropForeign(['ticket_type_id']);

            $table->foreign('ticket_type_id')
                ->references('id')
                ->on('ticket_types');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->text('two_factor_secret')
                ->after('cost_per_hour')
                ->nullable();
            $table->text('two_factor_recovery_codes')
                ->after('two_factor_secret')
                ->nullable();
            $table->timestamp('two_factor_confirmed_at')
                ->after('two_factor_recovery_codes')
                ->nullable();

            $table->dropForeign(['currency_id']);

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies');
        });

        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses');
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropColumn([
                'created_by',
                'updated_by',
            ]);
        });
    }
};
