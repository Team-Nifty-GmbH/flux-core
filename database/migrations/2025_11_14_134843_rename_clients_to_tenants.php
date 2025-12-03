<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // First rename clients table to tenants
        Schema::rename('clients', 'tenants');

        Schema::table('tenants', function (Blueprint $table): void {
            $table->renameColumn('client_code', 'tenant_code');
        });

        // AddressTypes
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('contact_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('addresses')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('addresses', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // BankConnectionTenant
        Schema::table('bank_connection_client', function (Blueprint $table): void {
            $table->dropForeign(['bank_connection_id']);
            $table->dropForeign(['client_id']);
        });

        Schema::rename('bank_connection_client', 'bank_connection_tenant');

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('bank_connection_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('bank_connection_tenant')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('bank_connections')
                ->cascadeOnDelete();

            $table->dropColumn('client_id');
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        // Employees
        Schema::table('employees', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('supervisor_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('employees')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('employees', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // OrderPositions
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('supplier_contact_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('order_positions')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // OrderTypes
        Schema::table('order_types', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('email_template_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('order_types')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('order_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // Orders
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // PaymentTypeTenant
        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['payment_type_id']);
        });

        Schema::rename('client_payment_type', 'payment_type_tenant');

        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('payment_type_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('payment_type_tenant')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('payment_type_id')
                ->references('id')
                ->on('payment_types')
                ->cascadeOnDelete();

            $table->dropColumn('client_id');
        });

        // ProductTenant
        Schema::table('client_product', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::rename('client_product', 'product_tenant');

        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('product_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('product_tenant')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->dropColumn('client_id');
        });

        // Projects
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('responsible_user_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('projects')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // PurchaseInvoices
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('payment_type_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
        });

        DB::table('purchase_invoices')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('client_id');
        });

        // SepaMandates
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('contact_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('sepa_mandates')
            ->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('client_id');
        });

        // SerialNumberRanges
        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        // TenantUser
        Schema::table('client_user', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::rename('client_user', 'tenant_user');

        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropPrimary(['tenant_id', 'user_id']);
            $table->primary(['tenant_id', 'user_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // TenantUser
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['user_id']);
            $table->dropPrimary(['tenant_id', 'user_id']);
        });

        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::rename('tenant_user', 'client_user');

        Schema::table('client_user', function (Blueprint $table): void {
            $table->primary(['client_id', 'user_id']);
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // SerialNumberRanges
        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->nullOnDelete();
        });

        // SepaMandates
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('sepa_mandates')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // PurchaseInvoices
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('approval_user_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
        });

        DB::table('purchase_invoices')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tenant_id');
        });

        // Projects
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('projects')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // ProductTenant
        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::rename('product_tenant', 'client_product');

        Schema::table('client_product', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('pivot_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('client_product')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('client_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->dropColumn('tenant_id');
        });

        // PaymentTypeTenant
        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->dropForeign(['payment_type_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::rename('payment_type_tenant', 'client_payment_type');

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('pivot_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('client_payment_type')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->foreign('payment_type_id')
                ->references('id')
                ->on('payment_types')
                ->cascadeOnDelete();

            $table->dropColumn('tenant_id');
        });

        // Orders
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // OrderTypes
        Schema::table('order_types', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('order_types')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('order_types', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // OrderPositions
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('order_positions')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Employees
        Schema::table('employees', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('employees')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('employees', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
        });

        // BankConnectionTenant
        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->dropForeign(['bank_connection_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::rename('bank_connection_tenant', 'bank_connection_client');

        Schema::table('bank_connection_client', function (Blueprint $table): void {
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('bank_connections')
                ->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->foreignId('client_id')
                ->nullable()
                ->after('uuid')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        DB::table('addresses')
            ->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('addresses', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('tenant_id');
        });

        // AddressTypes
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('address_types', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Rename tenants table to clients
        Schema::table('tenants', function (Blueprint $table): void {
            $table->renameColumn('tenant_code', 'client_code');
        });

        Schema::rename('tenants', 'clients');
    }
};
