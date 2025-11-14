<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Step 1: Drop foreign keys in pivot tables BEFORE renaming
        Schema::table('client_user', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('client_product', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('bank_connection_client', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        // Step 2: Rename main table
        Schema::rename('clients', 'tenants');

        // Step 3: Rename pivot tables
        Schema::rename('client_user', 'tenant_user');
        Schema::rename('client_payment_type', 'tenant_payment_type');
        Schema::rename('client_product', 'tenant_product');
        Schema::rename('bank_connection_client', 'bank_connection_tenant');

        // Rename client_id column in addresses table
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Rename client_id column in contacts table
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Rename client_id column in projects table
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename client_id column in orders table
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename client_id column in serial_number_ranges table
        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Rename client_id column in order_types table
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Rename client_id column in sepa_mandates table
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename client_id column in address_types table
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Rename client_id column in ledger_accounts table
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename client_id column in employees table
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename client_id column in purchase_invoices table (nullable)
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        // Update foreign keys in pivot tables
        // Note: Foreign keys were already dropped before table renames, so no need to drop again
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropPrimary(['tenant_id', 'user_id']);
            $table->primary(['tenant_id', 'user_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::table('tenant_payment_type', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('tenant_payment_type', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Step 1: Drop foreign keys in pivot tables (already renamed to tenant_*)
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropPrimary(['tenant_id', 'user_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('tenant_payment_type', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        // Step 2: Reverse client_id column in regular tables
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->nullOnDelete();
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        // Step 3: Rename columns back in pivot tables
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('tenant_payment_type', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        // Step 4: Rename pivot tables back
        Schema::rename('bank_connection_tenant', 'bank_connection_client');
        Schema::rename('tenant_product', 'client_product');
        Schema::rename('tenant_payment_type', 'client_payment_type');
        Schema::rename('tenant_user', 'client_user');

        // Step 5: Rename main table back
        Schema::rename('tenants', 'clients');

        // Step 6: Recreate foreign keys in pivot tables (now using original table names)
        Schema::table('client_user', function (Blueprint $table): void {
            $table->primary(['client_id', 'user_id']);
            $table->foreign('client_id')->references('id')->on('clients');
        });

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('client_product', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('bank_connection_client', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }
};
