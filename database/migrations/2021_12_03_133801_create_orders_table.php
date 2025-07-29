<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            // id
            $table->foreignId('address_delivery_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('address_invoice_id')
                ->constrained('addresses');
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('approval_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('contact_bank_connection_id')
                ->after('contact_id')
                ->nullable()
                ->constrained('contact_bank_connections')
                ->nullOnDelete();
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts');
            $table->foreignId('created_from_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('currency_id')
                ->constrained('currencies');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreignId('lead_id')
                ->nullable()
                ->constrained('leads')
                ->nullOnDelete();
            $table->unsignedBigInteger('order_type_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('payment_type_id');
            $table->foreignId('price_list_id')
                ->constrained('price_lists');
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('vat_rate_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();

            $table->json('address_invoice')->nullable();
            $table->json('address_delivery')->nullable();
            $table->string('state')->nullable();
            $table->string('payment_state')->nullable();
            $table->string('delivery_state')->nullable();

            $table->integer('payment_target', false, true);
            $table->integer('payment_discount_target', false, true)->nullable();
            $table->decimal('payment_discount_percent', 40, 10)->nullable();
            $table->decimal('shipping_costs_net_price', 40, 10)->default(0)->nullable()
                ->comment('A decimal containing the net price of shipping costs.');
            $table->decimal('shipping_costs_gross_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the gross price of shipping costs.');
            $table->decimal('shipping_costs_vat_price', 40, 10)
                ->nullable()
                ->comment('A decimal containing the vat price of shipping costs.');
            $table->decimal('shipping_costs_vat_rate_percentage', 40, 10)
                ->nullable()
                ->comment('A decimal, containing the vat-rate in percent for the shipping costs, that is cached for easier and faster readability of this order.');
            $table->decimal('total_base_net_price', 40, 10)->nullable();
            $table->decimal('total_base_gross_price', 40, 10)->nullable();
            $table->decimal('total_base_discounted_net_price', 40, 10)->nullable();
            $table->decimal('total_base_discounted_gross_price', 40, 10)->nullable();
            $table->decimal('gross_profit', 40, 10)->default(0);
            $table->decimal('total_purchase_price', 40, 10)->default(0);
            $table->decimal('total_cost', 40, 10)->nullable();
            $table->decimal('margin', 40, 10)->default(0);
            $table->decimal('total_net_price', 40, 10)->default(0);
            $table->decimal('total_gross_price', 40, 10)->default(0);
            $table->json('total_vats')->nullable();
            $table->decimal('total_discount_percentage', 40, 10)->nullable();
            $table->decimal('total_discount_flat', 40, 10)->nullable();
            $table->decimal('total_position_discount_percentage', 40, 10)->nullable();
            $table->decimal('total_position_discount_flat', 40, 10)->nullable();
            $table->decimal('balance', 40, 10)->nullable();
            $table->integer('payment_reminder_days_1', false, true);
            $table->integer('payment_reminder_days_2', false, true);
            $table->integer('payment_reminder_days_3', false, true);
            $table->unsignedInteger('payment_reminder_current_level')->nullable();
            $table->date('payment_reminder_next_date')->nullable();

            // string
            $table->string('order_number')->unique();
            $table->string('commission')->nullable();
            $table->string('iban')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();
            $table->longText('header')->nullable();
            $table->longText('footer')->nullable();
            $table->longText('logistic_note')->nullable();

            // date
            $table->date('order_date');
            $table->date('invoice_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('system_delivery_date')->nullable();
            $table->date('system_delivery_date_end')->nullable();
            $table->date('customer_delivery_date')->nullable();
            $table->date('date_of_approval')->nullable();

            // boolean
            $table->boolean('is_locked')->default(false)
                ->comment('If set to true this order cant be edited anymore, ' .
                    'this happens usually when the invoice was printed.');
            $table->boolean('is_imported')->default(false);
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('requires_approval')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['order_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
