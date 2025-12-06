<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    private function clientToTenant(string $table, bool $nullable = false, string $onDelete = 'cascade'): void
    {
        $this->replaceColumn($table, 'client_id', 'tenant_id', $nullable, $onDelete);
    }

    private function tenantToClient(string $table, bool $nullable = false, string $onDelete = 'cascade'): void
    {
        $this->replaceColumn($table, 'tenant_id', 'client_id', $nullable, $onDelete);
    }

    private function replaceColumn(
        string $table,
        string $oldColumn,
        string $newColumn,
        bool $nullable,
        string $onDelete
    ): void {
        Schema::table($table, function (Blueprint $table) use ($newColumn, $oldColumn): void {
            $table->unsignedBigInteger($newColumn)->nullable()->after($oldColumn);
        });

        DB::table($table)->update([$newColumn => DB::raw($oldColumn)]);

        Schema::table($table, function (Blueprint $table) use ($newColumn, $oldColumn, $nullable, $onDelete): void {
            if (! $nullable) {
                $table->unsignedBigInteger($newColumn)->nullable(false)->change();
            }
            $table->dropConstrainedForeignId($oldColumn);

            $fk = $table->foreign($newColumn)->references('id')->on('tenants');
            match ($onDelete) {
                'cascade' => $fk->cascadeOnDelete(),
                'null' => $fk->nullOnDelete(),
                default => null,
            };
        });
    }

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
        $this->clientToTenant('addresses');

        // BankConnectionTenant
        Schema::table('bank_connection_client', function (Blueprint $table): void {
            $table->dropForeign(['bank_connection_id']);
            $table->dropForeign(['client_id']);
        });

        Schema::rename('bank_connection_client', 'bank_connection_tenant');

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('bank_connection_id');
        });

        DB::table('bank_connection_tenant')->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropColumn('client_id');
            $table->foreign('bank_connection_id')->references('id')->on('bank_connections')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropUnique('contacts_customer_number_client_id_unique');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->unique(['customer_number', 'tenant_id']);
        });

        // Employees
        $this->clientToTenant('employees');

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropUnique('ledger_accounts_number_ledger_account_type_enum_client_id_unique');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['number', 'ledger_account_type_enum', 'tenant_id']);
        });

        // OrderPositions
        $this->clientToTenant('order_positions');

        // OrderTypes
        $this->clientToTenant('order_types');

        // Orders
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropUnique('orders_order_number_client_id_unique');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('client_id', 'tenant_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['order_number', 'tenant_id']);
        });

        // PaymentTypeTenant
        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['payment_type_id']);
        });

        Schema::rename('client_payment_type', 'payment_type_tenant');

        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('payment_type_id');
        });

        DB::table('payment_type_tenant')->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropColumn('client_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // ProductTenant
        Schema::table('client_product', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::rename('client_product', 'product_tenant');

        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('client_id');
        });

        DB::table('product_tenant')->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropColumn('client_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Projects
        $this->clientToTenant('projects');

        // PurchaseInvoices
        $this->clientToTenant('purchase_invoices', true, 'null');

        // SepaMandates - drop FK first before dropping unique index that it depends on
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropUnique('sepa_mandates_client_id_mandate_reference_number_unique');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('client_id');
        });

        DB::table('sepa_mandates')->update(['tenant_id' => DB::raw('client_id')]);

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->dropColumn('client_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'mandate_reference_number']);
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

        // SepaMandates - drop FK first before dropping unique index that it depends on
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('sepa_mandates_tenant_id_mandate_reference_number_unique');
        });

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable()->after('tenant_id');
        });

        DB::table('sepa_mandates')->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropColumn('tenant_id');
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['client_id', 'mandate_reference_number']);
        });

        // PurchaseInvoices
        $this->tenantToClient('purchase_invoices', true, 'null');

        // Projects
        $this->tenantToClient('projects');

        // ProductTenant
        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::rename('product_tenant', 'client_product');

        Schema::table('client_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable()->after('tenant_id');
        });

        DB::table('client_product')->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('client_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropColumn('tenant_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // PaymentTypeTenant
        Schema::table('payment_type_tenant', function (Blueprint $table): void {
            $table->dropForeign(['payment_type_id']);
            $table->dropForeign(['tenant_id']);
        });

        Schema::rename('payment_type_tenant', 'client_payment_type');

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable()->after('tenant_id');
        });

        DB::table('client_payment_type')->update(['client_id' => DB::raw('tenant_id')]);

        Schema::table('client_payment_type', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
            $table->dropColumn('tenant_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Orders
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('orders_order_number_tenant_id_unique');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['order_number', 'client_id']);
        });

        // OrderTypes
        $this->tenantToClient('order_types');

        // OrderPositions
        $this->tenantToClient('order_positions');

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('ledger_accounts_number_ledger_account_type_enum_tenant_id_unique');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['number', 'ledger_account_type_enum', 'client_id']);
        });

        // Employees
        $this->tenantToClient('employees');

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('contacts_customer_number_tenant_id_unique');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('tenant_id', 'client_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('tenants');
            $table->unique(['customer_number', 'client_id']);
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
        $this->tenantToClient('addresses');

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
