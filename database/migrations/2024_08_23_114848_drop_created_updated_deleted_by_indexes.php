<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AddressType
        Schema::table('address_types', function (Blueprint $table) {
            $table->dropIndex('address_types_created_by_foreign');
            $table->dropIndex('address_types_updated_by_foreign');
            $table->dropIndex('address_types_deleted_by_foreign');
        });

        // Address
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex('addresses_created_by_foreign');
            $table->dropIndex('addresses_updated_by_foreign');
            $table->dropIndex('addresses_deleted_by_foreign');
        });

        // Category
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_created_by_foreign');
            $table->dropIndex('categories_updated_by_foreign');
        });

        // Client
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_created_by_foreign');
            $table->dropIndex('clients_updated_by_foreign');
            $table->dropIndex('clients_deleted_by_foreign');
        });

        // Comment
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_created_by_foreign');
            $table->dropIndex('comments_updated_by_foreign');
            $table->dropIndex('comments_deleted_by_foreign');
        });

        // ContactBankConnection
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->dropIndex('contact_bank_connections_created_by_foreign');
            $table->dropIndex('contact_bank_connections_updated_by_foreign');
            $table->dropIndex('contact_bank_connections_deleted_by_foreign');
        });

        // ContactOption
        Schema::table('contact_options', function (Blueprint $table) {
            $table->dropIndex('contact_options_created_by_foreign');
            $table->dropIndex('contact_options_updated_by_foreign');
        });

        // Contact
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_created_by_foreign');
            $table->dropIndex('contacts_updated_by_foreign');
            $table->dropIndex('contacts_deleted_by_foreign');
        });

        // Country
        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex('countries_created_by_foreign');
            $table->dropIndex('countries_updated_by_foreign');
            $table->dropIndex('countries_deleted_by_foreign');
        });

        // CountryRegion
        Schema::table('country_regions', function (Blueprint $table) {
            $table->dropIndex('country_regions_created_by_foreign');
            $table->dropIndex('country_regions_updated_by_foreign');
            $table->dropIndex('country_regions_deleted_by_foreign');
        });

        // Currency
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropIndex('currencies_created_by_foreign');
            $table->dropIndex('currencies_updated_by_foreign');
            $table->dropIndex('currencies_deleted_by_foreign');
        });

        // Discount
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex('discounts_created_by_foreign');
            $table->dropIndex('discounts_updated_by_foreign');
            $table->dropIndex('discounts_deleted_by_foreign');
        });

        // InterfaceUser
        Schema::table('interface_users', function (Blueprint $table) {
            $table->dropIndex('interface_users_created_by_foreign');
            $table->dropIndex('interface_users_updated_by_foreign');
            $table->dropIndex('interface_users_deleted_by_foreign');
        });

        // Language
        Schema::table('languages', function (Blueprint $table) {
            $table->dropIndex('languages_created_by_foreign');
            $table->dropIndex('languages_updated_by_foreign');
            $table->dropIndex('languages_deleted_by_foreign');
        });

        // Lock
        Schema::table('locks', function (Blueprint $table) {
            $table->dropIndex('locks_created_by_foreign');
            $table->dropIndex('locks_updated_by_foreign');
        });

        // OrderPosition
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropIndex('order_positions_created_by_foreign');
            $table->dropIndex('order_positions_updated_by_foreign');
            $table->dropIndex('order_positions_deleted_by_foreign');
        });

        // OrderType
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropIndex('order_types_created_by_foreign');
            $table->dropIndex('order_types_updated_by_foreign');
            $table->dropIndex('order_types_deleted_by_foreign');
        });

        // Order
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_by_foreign');
            $table->dropIndex('orders_updated_by_foreign');
            $table->dropIndex('orders_deleted_by_foreign');
        });

        // PaymentType
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropIndex('payment_types_created_by_foreign');
            $table->dropIndex('payment_types_updated_by_foreign');
            $table->dropIndex('payment_types_deleted_by_foreign');
        });

        // PriceList
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropIndex('price_lists_created_by_foreign');
            $table->dropIndex('price_lists_updated_by_foreign');
            $table->dropIndex('price_lists_deleted_by_foreign');
        });

        // Price
        Schema::table('prices', function (Blueprint $table) {
            $table->dropIndex('prices_created_by_foreign');
            $table->dropIndex('prices_updated_by_foreign');
            $table->dropIndex('prices_deleted_by_foreign');
        });

        //ProductOptionGroup
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->dropIndex('product_option_groups_created_by_foreign');
            $table->dropIndex('product_option_groups_updated_by_foreign');
            $table->dropIndex('product_option_groups_deleted_by_foreign');
        });

        // ProductOption
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropIndex('product_options_created_by_foreign');
            $table->dropIndex('product_options_updated_by_foreign');
            $table->dropIndex('product_options_deleted_by_foreign');
        });

        // ProductProperty
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropIndex('product_properties_created_by_foreign');
            $table->dropIndex('product_properties_updated_by_foreign');
            $table->dropIndex('product_properties_deleted_by_foreign');
        });

        // Product
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_created_by_foreign');
            $table->dropIndex('products_updated_by_foreign');
            $table->dropIndex('products_deleted_by_foreign');
        });

        // Project
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_created_by_foreign');
            $table->dropIndex('projects_updated_by_foreign');
            $table->dropIndex('projects_deleted_by_foreign');
        });

        // SepaMandate
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropIndex('sepa_mandates_created_by_foreign');
            $table->dropIndex('sepa_mandates_updated_by_foreign');
            $table->dropIndex('sepa_mandates_deleted_by_foreign');
        });

        // SerialNumberRange
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->dropIndex('serial_number_ranges_created_by_foreign');
            $table->dropIndex('serial_number_ranges_updated_by_foreign');
            $table->dropIndex('serial_number_ranges_deleted_by_foreign');
        });

        // SerialNumber
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropIndex('serial_numbers_created_by_foreign');
            $table->dropIndex('serial_numbers_updated_by_foreign');
        });

        // Snapshot
        Schema::table('snapshots', function (Blueprint $table) {
            $table->dropIndex('snapshots_created_by_foreign');
            $table->dropIndex('snapshots_updated_by_foreign');
        });

        // StockPosting
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->dropIndex('stock_postings_created_by_foreign');
            $table->dropIndex('stock_postings_updated_by_foreign');
            $table->dropIndex('stock_postings_deleted_by_foreign');
        });

        // Task
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('project_tasks_created_by_foreign');
            $table->dropIndex('project_tasks_updated_by_foreign');
            $table->dropIndex('project_tasks_deleted_by_foreign');
        });

        // Ticket
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_created_by_foreign');
            $table->dropIndex('tickets_updated_by_foreign');
            $table->dropIndex('tickets_deleted_by_foreign');
        });

        // Unit
        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('units_created_by_foreign');
            $table->dropIndex('units_updated_by_foreign');
            $table->dropIndex('units_deleted_by_foreign');
        });

        // User
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_by_foreign');
            $table->dropIndex('users_updated_by_foreign');
            $table->dropIndex('users_deleted_by_foreign');
        });

        // VatRate
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->dropIndex('vat_rates_created_by_foreign');
            $table->dropIndex('vat_rates_updated_by_foreign');
            $table->dropIndex('vat_rates_deleted_by_foreign');
        });

        // Warehouse
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropIndex('warehouses_created_by_foreign');
            $table->dropIndex('warehouses_updated_by_foreign');
            $table->dropIndex('warehouses_deleted_by_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('address_types', function (Blueprint $table) {
            $table->index('created_by', 'address_types_created_by_foreign');
            $table->index('updated_by', 'address_types_updated_by_foreign');
            $table->index('deleted_by', 'address_types_deleted_by_foreign');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->index('created_by', 'addresses_created_by_foreign');
            $table->index('updated_by', 'addresses_updated_by_foreign');
            $table->index('deleted_by', 'addresses_deleted_by_foreign');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('created_by', 'categories_created_by_foreign');
            $table->index('updated_by', 'categories_updated_by_foreign');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('created_by', 'clients_created_by_foreign');
            $table->index('updated_by', 'clients_updated_by_foreign');
            $table->index('deleted_by', 'clients_deleted_by_foreign');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_by', 'comments_created_by_foreign');
            $table->index('updated_by', 'comments_updated_by_foreign');
            $table->index('deleted_by', 'comments_deleted_by_foreign');
        });

        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->index('created_by', 'contact_bank_connections_created_by_foreign');
            $table->index('updated_by', 'contact_bank_connections_updated_by_foreign');
            $table->index('deleted_by', 'contact_bank_connections_deleted_by_foreign');
        });

        Schema::table('contact_options', function (Blueprint $table) {
            $table->index('created_by', 'contact_options_created_by_foreign');
            $table->index('updated_by', 'contact_options_updated_by_foreign');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('created_by', 'contacts_created_by_foreign');
            $table->index('updated_by', 'contacts_updated_by_foreign');
            $table->index('deleted_by', 'contacts_deleted_by_foreign');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->index('created_by', 'countries_created_by_foreign');
            $table->index('updated_by', 'countries_updated_by_foreign');
            $table->index('deleted_by', 'countries_deleted_by_foreign');
        });

        Schema::table('country_regions', function (Blueprint $table) {
            $table->index('created_by', 'country_regions_created_by_foreign');
            $table->index('updated_by', 'country_regions_updated_by_foreign');
            $table->index('deleted_by', 'country_regions_deleted_by_foreign');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->index('created_by', 'currencies_created_by_foreign');
            $table->index('updated_by', 'currencies_updated_by_foreign');
            $table->index('deleted_by', 'currencies_deleted_by_foreign');
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->index('created_by', 'discounts_created_by_foreign');
            $table->index('updated_by', 'discounts_updated_by_foreign');
            $table->index('deleted_by', 'discounts_deleted_by_foreign');
        });

        Schema::table('interface_users', function (Blueprint $table) {
            $table->index('created_by', 'interface_users_created_by_foreign');
            $table->index('updated_by', 'interface_users_updated_by_foreign');
            $table->index('deleted_by', 'interface_users_deleted_by_foreign');
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->index('created_by', 'languages_created_by_foreign');
            $table->index('updated_by', 'languages_updated_by_foreign');
            $table->index('deleted_by', 'languages_deleted_by_foreign');
        });

        Schema::table('locks', function (Blueprint $table) {
            $table->index('created_by', 'locks_created_by_foreign');
            $table->index('updated_by', 'locks_updated_by_foreign');
        });

        Schema::table('order_positions', function (Blueprint $table) {
            $table->index('created_by', 'order_positions_created_by_foreign');
            $table->index('updated_by', 'order_positions_updated_by_foreign');
            $table->index('deleted_by', 'order_positions_deleted_by_foreign');
        });

        Schema::table('order_types', function (Blueprint $table) {
            $table->index('created_by', 'order_types_created_by_foreign');
            $table->index('updated_by', 'order_types_updated_by_foreign');
            $table->index('deleted_by', 'order_types_deleted_by_foreign');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_by', 'orders_created_by_foreign');
            $table->index('updated_by', 'orders_updated_by_foreign');
            $table->index('deleted_by', 'orders_deleted_by_foreign');
        });

        Schema::table('payment_types', function (Blueprint $table) {
            $table->index('created_by', 'payment_types_created_by_foreign');
            $table->index('updated_by', 'payment_types_updated_by_foreign');
            $table->index('deleted_by', 'payment_types_deleted_by_foreign');
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->index('created_by', 'price_lists_created_by_foreign');
            $table->index('updated_by', 'price_lists_updated_by_foreign');
            $table->index('deleted_by', 'price_lists_deleted_by_foreign');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->index('created_by', 'prices_created_by_foreign');
            $table->index('updated_by', 'prices_updated_by_foreign');
            $table->index('deleted_by', 'prices_deleted_by_foreign');
        });

        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->index('created_by', 'product_option_groups_created_by_foreign');
            $table->index('updated_by', 'product_option_groups_updated_by_foreign');
            $table->index('deleted_by', 'product_option_groups_deleted_by_foreign');
        });

        Schema::table('product_options', function (Blueprint $table) {
            $table->index('created_by', 'product_options_created_by_foreign');
            $table->index('updated_by', 'product_options_updated_by_foreign');
            $table->index('deleted_by', 'product_options_deleted_by_foreign');
        });

        Schema::table('product_properties', function (Blueprint $table) {
            $table->index('created_by', 'product_properties_created_by_foreign');
            $table->index('updated_by', 'product_properties_updated_by_foreign');
            $table->index('deleted_by', 'product_properties_deleted_by_foreign');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('created_by', 'products_created_by_foreign');
            $table->index('updated_by', 'products_updated_by_foreign');
            $table->index('deleted_by', 'products_deleted_by_foreign');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('created_by', 'projects_created_by_foreign');
            $table->index('updated_by', 'projects_updated_by_foreign');
            $table->index('deleted_by', 'projects_deleted_by_foreign');
        });

        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->index('created_by', 'sepa_mandates_created_by_foreign');
            $table->index('updated_by', 'sepa_mandates_updated_by_foreign');
            $table->index('deleted_by', 'sepa_mandates_deleted_by_foreign');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->index('created_by', 'serial_number_ranges_created_by_foreign');
            $table->index('updated_by', 'serial_number_ranges_updated_by_foreign');
            $table->index('deleted_by', 'serial_number_ranges_deleted_by_foreign');
        });

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->index('created_by', 'serial_numbers_created_by_foreign');
            $table->index('updated_by', 'serial_numbers_updated_by_foreign');
        });

        Schema::table('snapshots', function (Blueprint $table) {
            $table->index('created_by', 'snapshots_created_by_foreign');
            $table->index('updated_by', 'snapshots_updated_by_foreign');
        });

        Schema::table('stock_postings', function (Blueprint $table) {
            $table->index('created_by', 'stock_postings_created_by_foreign');
            $table->index('updated_by', 'stock_postings_updated_by_foreign');
            $table->index('deleted_by', 'stock_postings_deleted_by_foreign');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('created_by', 'project_tasks_created_by_foreign');
            $table->index('updated_by', 'project_tasks_updated_by_foreign');
            $table->index('deleted_by', 'project_tasks_deleted_by_foreign');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('created_by', 'tickets_created_by_foreign');
            $table->index('updated_by', 'tickets_updated_by_foreign');
            $table->index('deleted_by', 'tickets_deleted_by_foreign');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->index('created_by', 'units_created_by_foreign');
            $table->index('updated_by', 'units_updated_by_foreign');
            $table->index('deleted_by', 'units_deleted_by_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('created_by', 'users_created_by_foreign');
            $table->index('updated_by', 'users_updated_by_foreign');
            $table->index('deleted_by', 'users_deleted_by_foreign');
        });

        Schema::table('vat_rates', function (Blueprint $table) {
            $table->index('created_by', 'vat_rates_created_by_foreign');
            $table->index('updated_by', 'vat_rates_updated_by_foreign');
            $table->index('deleted_by', 'vat_rates_deleted_by_foreign');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->index('created_by', 'warehouses_created_by_foreign');
            $table->index('updated_by', 'warehouses_updated_by_foreign');
            $table->index('deleted_by', 'warehouses_deleted_by_foreign');
        });
    }
};
