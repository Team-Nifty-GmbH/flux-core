<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // AddressAddressType
        Schema::table('address_address_type', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->cascadeOnDelete();
            $table->unique(['address_id', 'address_type_id']);
        });

        // AddressAddressTypeOrder
        Schema::table('address_address_type_order', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropForeign(['order_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->unsignedBigInteger('order_id')->after('address_type_id')->change();

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
            $table->unique(['address_id', 'address_type_id', 'order_id'], 'address_address_type_order_unique');
        });

        // AddressProduct
        Schema::table('address_product', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['product_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();

            $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['address_id', 'product_id']);
        });

        // AddressSerialNumber
        Schema::table('address_serial_number', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // AddressTypes
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex('address_types_client_id_address_type_code_unique');
            $table->dropColumn([
                'is_locked',
                'is_unique',
            ]);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'address_type_code']);
        });

        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropColumn('is_dark_mode');

            $table->unsignedBigInteger('country_id')->nullable()->after('contact_id')->change();
            $table->unsignedBigInteger('language_id')->nullable()->after('country_id')->change();
            $table->string('remember_token', 100)
                ->nullable()
                ->after('advertising_state')
                ->change();
            $table->boolean('can_login')->default(false)->after('remember_token')->change();
            $table->boolean('is_active')->default(true)->after('has_formal_salutation')->change();
            $table->boolean('is_delivery_address')->default(false)->after('is_active')->change();
            $table->boolean('is_invoice_address')
                ->default(false)
                ->after('is_delivery_address')
                ->change();
        });

        // BankConnectionTenant
        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unique(['bank_connection_id', 'tenant_id']);
        });

        // CalendarEvents
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->boolean('is_all_day')->default(false)->after('has_taken_place')->change();
        });

        // CalendarGroups
        Schema::dropIfExists('calendar_groups');

        // Calendarable
        Schema::table('calendarables', function (Blueprint $table): void {
            $table->dropForeign(['calendar_id']);
            $table->dropIndex('calendarables_calendarable_type_calendarable_id_index');
        });

        Schema::rename('calendarables', 'calendarable');
        Schema::table('calendarable', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->dropColumn([
                'created_at',
                'updated_at',
            ]);

            $table->foreign('calendar_id')->references('id')->on('calendars')->cascadeOnDelete();
            $table->index(['calendarable_type', 'calendarable_id']);
        });

        // Carts
        Schema::table('carts', function (Blueprint $table): void {
            $table->string('authenticatable_type')->nullable()->after('price_list_id')->change();
            $table->unsignedBigInteger('authenticatable_id')
                ->nullable()
                ->after('authenticatable_type')
                ->change();
        });

        // Categories
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('parent_id')->nullable()->after('uuid')->change();
        });

        // Categorizable
        Schema::table('categorizables', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropUnique('categorizables_ids_type_unique');
            $table->dropIndex('categorizables_categorizable_type_categorizable_id_index');
        });
        Schema::rename('categorizables', 'categorizable');
        Schema::table('categorizable', function (Blueprint $table): void {
            $table->id('pivot_id')->first();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['category_id', 'categorizable_type', 'categorizable_id'], 'categorizable_unique');
        });

        // CategoryPriceList
        Schema::table('category_price_list', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['price_list_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->unsignedBigInteger('discount_id')->after('category_id')->change();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->cascadeOnDelete();
            $table->unique(['category_id', 'price_list_id']);
        });

        // Comments
        Schema::table('comments', function (Blueprint $table): void {
            $table->unsignedBigInteger('parent_id')->nullable()->after('uuid')->change();
        });

        // CommissionRates
        Schema::table('commission_rates', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_id')->nullable()->after('category_id')->change();
            $table->unsignedBigInteger('user_id')->after('product_id')->change();
        });

        // Commissions
        Schema::table('commissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('credit_note_order_position_id')
                ->nullable()
                ->after('commission_rate_id')
                ->change();
            $table->unsignedBigInteger('user_id')->after('order_position_id')->change();
        });

        // Communicatable
        Schema::table('communicatable', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->string('communicatable_type')->after('communication_id')->change();
            $table->unsignedBigInteger('communicatable_id')
                ->after('communicatable_type')
                ->change();
        });

        // ContactDiscount
        Schema::table('contact_discount', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // ContactDiscountGroup
        Schema::table('contact_discount_group', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // ContactIndustry
        Schema::table('contact_industry', function (Blueprint $table) {
            $table->unique(['contact_id', 'industry_id']);
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->unsignedBigInteger('agent_id')->nullable()->after('uuid')->change();
            $table->unsignedBigInteger('currency_id')->nullable()->after('approval_user_id')->change();
            $table->unsignedBigInteger('delivery_address_id')->nullable()->after('currency_id')->change();
            $table->unsignedBigInteger('expense_ledger_account_id')
                ->nullable()
                ->after('delivery_address_id')
                ->change();
            $table->unsignedBigInteger('invoice_address_id')
                ->nullable()
                ->after('expense_ledger_account_id')
                ->change();
            $table->unsignedBigInteger('main_address_id')
                ->nullable()
                ->after('invoice_address_id')
                ->change();
            $table->unsignedBigInteger('purchase_payment_type_id')
                ->nullable()
                ->after('price_list_id')
                ->change();
            $table->unsignedBigInteger('tenant_id')->after('record_origin_id')->change();
            $table->boolean('has_sensitive_reminder')
                ->default(false)
                ->after('has_delivery_lock')
                ->change();
        });

        // Countries
        Schema::table('countries', function (Blueprint $table): void {
            $table->unsignedBigInteger('language_id')->after('currency_id')->change();
        });

        // DiscountDiscountGroup
        Schema::table('discount_discount_group', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unsignedBigInteger('discount_id')->after('discount_group_id')->change();
            $table->unique(['discount_group_id', 'discount_id']);
        });

        // EventSubscriptions
        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->string('subscribable_type')->after('id')->change();
            $table->unsignedBigInteger('subscribable_id')->after('subscribable_type')->change();
        });

        // Industries
        Schema::table('industries', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->timestamp('deleted_at')->nullable()->after('updated_by');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // Inviteables
        Schema::dropIfExists('inviteables');

        // LeadStates
        Schema::table('lead_states', function (Blueprint $table): void {
            $table->boolean('is_won')->default(false)->after('is_lost')->change();
        });

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->timestamp('deleted_at')->nullable()->after('updated_by');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // MailAccountUser
        Schema::table('mail_account_user', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unsignedBigInteger('user_id')->after('mail_account_id')->change();
            $table->unique(['mail_account_id', 'user_id']);
        });

        // MailAccounts
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('name')->after('uuid')->change();
            $table->renameColumn('is_auto_assign', 'has_auto_assign');
            $table->renameColumn('is_o_auth', 'has_o_auth');
        });

        // MailFolders
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->boolean('can_create_purchase_invoice')
                ->default(false)
                ->after('can_create_lead')
                ->change();
            $table->boolean('can_create_ticket')
                ->default(false)
                ->after('can_create_purchase_invoice')
                ->change();
        });

        // OrderPaymentRun
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // OrderPositionStockPosting
        Schema::table('order_position_stock_posting', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // OrderPositionTask
        Schema::table('order_position_task', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // OrderPositions
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('ledger_account_id')
                ->nullable()
                ->after('credit_account_id')
                ->change();
            $table->unsignedBigInteger('order_id')->after('ledger_account_id')->change();
            $table->unsignedBigInteger('origin_position_id')->nullable()->after('order_id')->change();
            $table->unsignedBigInteger('parent_id')->nullable()->after('origin_position_id')->change();
            $table->boolean('is_bundle_position')
                ->default(false)
                ->after('is_alternative')
                ->change();
            $table->boolean('is_free_text')
                ->default(false)
                ->after('is_bundle_position')
                ->change();
        });

        // OrderSchedule
        Schema::table('order_schedule', function (Blueprint $table) {
            $table->unique(['order_id', 'schedule_id']);
        });

        // OrderTransaction
        Schema::table('order_transaction', function (Blueprint $table): void {
            $table->unsignedBigInteger('transaction_id')->after('order_id')->change();
        });

        // OrderTypes
        Schema::table('order_types', function (Blueprint $table): void {
            $table->boolean('is_visible_in_sidebar')->default(true)->after('is_hidden')->change();
        });

        // OrderUser
        Schema::table('order_user', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unique(['order_id', 'user_id']);
        });

        // Orders
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('address_delivery_id')->nullable()->after('uuid')->change();
            $table->unsignedBigInteger('address_invoice_id')->after('address_delivery_id')->change();
            $table->unsignedBigInteger('agent_id')->nullable()->after('address_invoice_id')->change();
            $table->unsignedBigInteger('contact_bank_connection_id')
                ->nullable()
                ->after('approval_user_id')
                ->change();
            $table->unsignedBigInteger('created_from_id')->nullable()->after('contact_id')->change();
            $table->unsignedBigInteger('lead_id')->nullable()->after('language_id')->change();
            $table->unsignedBigInteger('parent_id')->nullable()->after('order_type_id')->change();
            $table->unsignedBigInteger('payment_type_id')->nullable()->after('parent_id')->change();
            $table->unsignedBigInteger('tenant_id')->after('responsible_user_id')->change();

            $table->boolean('is_imported')->default(false)->after('is_confirmed')->change();
            $table->boolean('is_locked')->default(false)->after('is_imported')->change();
        });

        // PaymentReminders
        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->after('media_id')->change();
        });

        // PaymentRuns
        Schema::table('payment_runs', function (Blueprint $table): void {
            $table->boolean('is_single_booking')
                ->default(true)
                ->after('is_instant_payment')
                ->change();
        });

        // PaymentTypeTenant
        Schema::table('payment_type_tenant', function (Blueprint $table) {
            $table->unique(['payment_type_id', 'tenant_id']);
        });

        // PaymentTypes
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->boolean('is_direct_debit')->default(false)->after('is_default')->change();
        });

        // PriceLists
        Schema::table('price_lists', function (Blueprint $table): void {
            $table->boolean('is_net')->default(true)->after('is_default')->change();
        });

        // Prices
        Schema::table('prices', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->after('price_list_id')->change();
        });

        // ProductBundleProduct
        Schema::table('product_bundle_product', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['bundle_product_id']);
        });

        Schema::rename('product_bundle_product', 'bundle_product_product');
        Schema::table('bundle_product_product', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unsignedBigInteger('product_id')->after('bundle_product_id')->change();
            $table->foreign('bundle_product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        // ProductCrossSellingProduct
        Schema::table('product_cross_selling_product', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // ProductProductOption
        Schema::table('product_product_option', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
        });

        // ProductProductProperty
        Schema::table('product_product_property', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_prop_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->renameColumn('product_prop_id', 'product_property_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_property_id')
                ->references('id')
                ->on('product_properties')
                ->cascadeOnDelete();
            $table->unique(['product_id', 'product_property_id']);
        });

        // ProductSupplier
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropIndex('product_supplier_product_id_contact_id_unique');
            $table->renameColumn('id', 'pivot_id');
            $table->unsignedBigInteger('product_id')->after('contact_id')->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['contact_id', 'product_id']);
        });

        // ProductTenant
        Schema::table('product_tenant', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->after('product_id')->change();
            $table->unique(['product_id', 'tenant_id']);
        });

        // Products
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('purchase_unit_id')->nullable()->after('parent_id')->change();
            $table->unsignedBigInteger('reference_unit_id')
                ->nullable()
                ->after('purchase_unit_id')
                ->change();
            $table->unsignedBigInteger('vat_rate_id')->nullable()->after('unit_id')->change();

            $table->boolean('has_serial_numbers')
                ->default(false)
                ->after('warning_stock_amount')
                ->change();
            $table->boolean('is_active_export_to_web_shop')
                ->default(false)
                ->after('is_active')
                ->change();
            $table->boolean('is_bundle')
                ->default(false)
                ->after('is_active_export_to_web_shop')
                ->change();
            $table->boolean('is_nos')->default(false)->after('is_highlight')->change();
        });

        // Projects
        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('parent_id')->nullable()->after('order_id')->change();
        });

        // PurchaseInvoicePositions
        Schema::table('purchase_invoice_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('purchase_invoice_id')->after('product_id')->change();
        });

        // QueueMonitorable
        Schema::table('queue_monitorables', function (Blueprint $table): void {
            $table->dropForeign(['queue_monitor_id']);
        });
        Schema::rename('queue_monitorables', 'queue_monitorable');
        Schema::table('queue_monitorable', function (Blueprint $table): void {
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->foreign('queue_monitor_id')
                ->references('id')
                ->on('queue_monitors')
                ->cascadeOnDelete();
            $table->unique(
                ['queue_monitor_id', 'queue_monitorable_type', 'queue_monitorable_id'],
                'queue_monitorable_unique'
            );
        });

        // SepaMandates
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_id')->after('contact_bank_connection_id')->change();
            $table->unsignedBigInteger('tenant_id')->after('contact_id')->change();
        });

        // SerialNumberRanges
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->string('unique_key')->after('tenant_id')->change();
        });

        // StockPostings
        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->unsignedBigInteger('parent_id')->nullable()->after('order_position_id')->change();
            $table->unsignedBigInteger('product_id')->after('parent_id')->change();
            $table->unsignedBigInteger('warehouse_id')->after('serial_number_id')->change();
        });

        // TaskUser
        Schema::table('task_user', function (Blueprint $table): void {
            $table->renameColumn('id', 'pivot_id');
            $table->unique(['task_id', 'user_id']);
        });

        // Tasks
        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_position_id')->nullable()->after('uuid')->change();
        });

        // TenantUser
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['user_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['tenant_id', 'user_id']);
        });

        // Tenants
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign('clients_commission_credit_note_order_type_id_foreign');
            $table->dropIndex('clients_commission_credit_note_order_type_id_foreign');
            $table->dropForeign('clients_country_id_foreign');
            $table->dropIndex('clients_country_id_foreign');
            $table->dropUnique('clients_client_code_unique');
            $table->unsignedBigInteger('country_id')
                ->nullable()
                ->after('commission_credit_note_order_type_id')
                ->change();
            $table->foreign('commission_credit_note_order_type_id')
                ->references('id')
                ->on('order_types')
                ->nullOnDelete();
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->nullOnDelete();
            $table->unique('tenant_code');
        });

        // TicketUser
        Schema::table('ticket_user', function (Blueprint $table): void {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['user_id']);
            $table->dropPrimary();
            $table->id('pivot_id')->first();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['ticket_id', 'user_id']);
        });

        // Tickets
        Schema::table('tickets', function (Blueprint $table): void {
            $table->unsignedBigInteger('ticket_type_id')->nullable()->after('uuid')->change();
        });

        // Users
        Schema::table('users', function (Blueprint $table): void {
            $table->char('uuid', 36)->after('id')->change();
            $table->unsignedBigInteger('currency_id')->nullable()->after('contact_id')->change();
            $table->unsignedBigInteger('language_id')->after('currency_id')->change();
            $table->unsignedBigInteger('parent_id')->nullable()->after('language_id')->change();
            $table->string('remember_token', 100)->nullable()->after('cost_per_hour')->change();
            $table->renameColumn('is_dark_mode', 'has_dark_mode');
            $table->boolean('is_active')->default(true)->after('has_dark_mode')->change();
        });

        // WorkTimes
        Schema::table('work_times', function (Blueprint $table): void {
            $table->unsignedBigInteger('employee_id')->nullable()->after('contact_id')->change();
            $table->unsignedBigInteger('user_id')->nullable()->after('parent_id')->change();
        });
    }

    public function down(): void
    {
        // AddressAddressType
        Schema::table('address_address_type', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropUnique('address_address_type_address_id_address_type_id_unique');
            $table->dropColumn('pivot_id');
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->cascadeOnDelete();
            $table->primary(['address_id', 'address_type_id']);
        });

        // AddressAddressTypeOrder
        Schema::table('address_address_type_order', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_type_id']);
            $table->dropForeign(['order_id']);
            $table->dropUnique('address_address_type_order_unique');
            $table->dropColumn('pivot_id');
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
            $table->primary(['address_id', 'address_type_id', 'order_id']);
        });

        // AddressProduct
        Schema::table('address_product', function (Blueprint $table): void {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['product_id']);
            $table->dropUnique('address_product_address_id_product_id_unique');
            $table->dropColumn('pivot_id');
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->primary(['address_id', 'product_id']);
        });

        // AddressSerialNumber
        Schema::table('address_serial_number', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // AddressTypes
        Schema::table('address_types', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex('address_types_tenant_id_address_type_code_unique');
            $table->boolean('is_locked')->default(false)->after('name');
            $table->boolean('is_unique')->default(false)->after('is_locked');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(
                ['tenant_id', 'address_type_code'],
                'address_types_client_id_address_type_code_unique'
            );
        });

        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->boolean('is_dark_mode')->default(false)->after('is_active');
        });

        // BankConnectionTenant
        Schema::table('bank_connection_tenant', function (Blueprint $table): void {
            $table->dropForeign(['bank_connection_id']);
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('bank_connection_tenant_bank_connection_id_tenant_id_unique');
            $table->renameColumn('pivot_id', 'id');
            $table->foreign('bank_connection_id')
                ->references('id')
                ->on('bank_connections')
                ->cascadeOnDelete();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });

        // CalendarGroups
        Schema::create('calendar_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->references('id')
                ->on('calendar_groups')
                ->cascadeOnDelete();
            $table->morphs('calendarable');
            $table->foreignId('calendar_id')
                ->nullable()
                ->references('id')
                ->on('calendars')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // Calendarable
        Schema::table('calendarable', function (Blueprint $table): void {
            $table->dropForeign(['calendar_id']);
            $table->dropUnique('calendarable_unique');
            $table->dropIndex('calendarable_calendarable_type_calendarable_id_index');
        });
        Schema::rename('calendarable', 'calendarables');
        Schema::table('calendarables', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->timestamps();

            $table->foreign('calendar_id')->references('id')->on('calendars')->cascadeOnDelete();
            $table->index(['calendarable_type', 'calendarable_id']);
        });

        // Categorizable
        Schema::table('categorizable', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropUnique('categorizable_unique');
        });
        Schema::rename('categorizable', 'categorizables');
        Schema::table('categorizables', function (Blueprint $table): void {
            $table->dropColumn('pivot_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(
                ['category_id', 'categorizable_id', 'categorizable_type'],
                'categorizables_ids_type_unique'
            );
            $table->index(['categorizable_type', 'categorizable_id']);
        });

        // CategoryPriceList
        Schema::table('category_price_list', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['price_list_id']);
            $table->dropUnique('category_price_list_category_id_price_list_id_unique');
            $table->dropColumn('pivot_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->cascadeOnDelete();
            $table->primary(['category_id', 'price_list_id']);
        });

        // Communicatable
        Schema::table('communicatable', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // ContactDiscount
        Schema::table('contact_discount', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // ContactDiscountGroup
        Schema::table('contact_discount_group', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        Schema::table('contact_industry', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropUnique('contact_industry_contact_id_industry_id_unique');
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
        });

        // DiscountDiscountGroup
        Schema::table('discount_discount_group', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->dropForeign(['discount_group_id']);
            $table->dropUnique('discount_discount_group_discount_group_id_discount_id_unique');
            $table->foreign('discount_group_id')
                ->references('id')
                ->on('discount_groups')
                ->cascadeOnDelete();
        });

        // Industries
        Schema::table('industries', function (Blueprint $table): void {
            $table->dropColumn([
                'created_by',
                'updated_by',
                'deleted_at',
                'deleted_by',
            ]);
        });

        // Inviteables
        Schema::create('inviteables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('calendar_event_id')
                ->references('id')
                ->on('calendar_events')
                ->cascadeOnDelete();
            $table->foreignId('model_calendar_id')
                ->nullable()
                ->references('id')
                ->on('calendars')
                ->cascadeOnDelete();
            $table->morphs('inviteable');
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        // LedgerAccounts
        Schema::table('ledger_accounts', function (Blueprint $table): void {
            $table->dropColumn([
                'created_by',
                'updated_by',
                'deleted_at',
                'deleted_by',
            ]);
        });

        // MailAccountUser
        Schema::table('mail_account_user', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->dropForeign(['mail_account_id']);
            $table->dropUnique('mail_account_user_mail_account_id_user_id_unique');
            $table->foreign('mail_account_id')
                ->references('id')
                ->on('mail_accounts')
                ->cascadeOnDelete();
        });

        // MailAccounts
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->renameColumn('has_auto_assign', 'is_auto_assign');
            $table->renameColumn('has_o_auth', 'is_o_auth');
        });

        // OrderPaymentRun
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // OrderPositionStockPosting
        Schema::table('order_position_stock_posting', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // OrderPositionTask
        Schema::table('order_position_task', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // OrderSchedule
        Schema::table('order_schedule', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique('order_schedule_order_id_schedule_id_unique');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        // OrderUser
        Schema::table('order_user', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->dropForeign(['order_id']);
            $table->dropUnique('order_user_order_id_user_id_unique');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        // PaymentTypeTenant
        Schema::table('payment_type_tenant', function (Blueprint $table) {
            $table->dropForeign(['payment_type_id']);
            $table->dropUnique('payment_type_tenant_payment_type_id_tenant_id_unique');
            $table->foreign('payment_type_id')
                ->references('id')
                ->on('payment_types')
                ->cascadeOnDelete();
        });

        // ProductBundleProduct
        Schema::table('bundle_product_product', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['bundle_product_id']);
        });
        Schema::rename('bundle_product_product', 'product_bundle_product');
        Schema::table('product_bundle_product', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->foreign('bundle_product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        // ProductCrossSellingProduct
        Schema::table('product_cross_selling_product', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // ProductProductOption
        Schema::table('product_product_option', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
        });

        // ProductProductProperty
        Schema::table('product_product_property', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_property_id']);
            $table->dropUnique('product_product_property_product_id_product_property_id_unique');
            $table->dropColumn('pivot_id');
            $table->renameColumn('product_property_id', 'product_prop_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_prop_id')
                ->references('id')
                ->on('product_properties')
                ->cascadeOnDelete();
            $table->primary(['product_id', 'product_prop_id']);
        });

        // ProductSupplier
        Schema::table('product_supplier', function (Blueprint $table): void {
            $table->dropForeign(['contact_id']);
            $table->dropIndex('product_supplier_contact_id_product_id_unique');
            $table->renameColumn('pivot_id', 'id');
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->unique(['product_id', 'contact_id']);
        });

        // ProductTenant
        Schema::table('product_tenant', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropUnique('product_tenant_product_id_tenant_id_unique');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // QueueMonitorable
        Schema::table('queue_monitorable', function (Blueprint $table): void {
            $table->dropForeign(['queue_monitor_id']);
            $table->dropUnique('queue_monitorable_unique');
        });
        Schema::rename('queue_monitorable', 'queue_monitorables');
        Schema::table('queue_monitorables', function (Blueprint $table): void {
            $table->dropColumn('pivot_id');
            $table->foreign('queue_monitor_id')
                ->references('id')
                ->on('queue_monitors')
                ->cascadeOnDelete();
            $table->primary(['queue_monitor_id', 'queue_monitorable_id', 'queue_monitorable_type']);
        });

        // TaskUser
        Schema::table('task_user', function (Blueprint $table): void {
            $table->renameColumn('pivot_id', 'id');
            $table->dropForeign(['task_id']);
            $table->dropUnique('task_user_task_id_user_id_unique');
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });

        // TenantUser
        Schema::table('tenant_user', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique('tenant_user_tenant_id_user_id_unique');
            $table->dropColumn('pivot_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->primary(['tenant_id', 'user_id']);
        });

        // Tenants
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign(['commission_credit_note_order_type_id']);
            $table->dropForeign(['country_id']);
            $table->dropUnique('tenants_tenant_code_unique');
            $table->foreign(
                'commission_credit_note_order_type_id',
                'clients_commission_credit_note_order_type_id_foreign'
            )
                ->references('id')
                ->on('order_types')
                ->nullOnDelete();
            $table->foreign('country_id', 'clients_country_id_foreign')
                ->references('id')
                ->on('countries')
                ->nullOnDelete();
            $table->unique('tenant_code', 'clients_client_code_unique');
        });

        // TicketUser
        Schema::table('ticket_user', function (Blueprint $table): void {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique('ticket_user_ticket_id_user_id_unique');
            $table->dropColumn('pivot_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->primary(['ticket_id', 'user_id']);
        });

        // Users
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('has_dark_mode', 'is_dark_mode');
        });
    }
};
