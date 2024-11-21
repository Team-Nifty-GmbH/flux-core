<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('approval_user_id')->nullable()->index('orders_approval_user_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('orders_parent_id_foreign');
            $table->unsignedBigInteger('client_id')->index('orders_client_id_foreign');
            $table->unsignedBigInteger('agent_id')->nullable()->index('orders_agent_id_foreign');
            $table->unsignedBigInteger('contact_id')->nullable()->index('orders_contact_id_foreign');
            $table->unsignedBigInteger('contact_bank_connection_id')->nullable()->index('orders_contact_bank_connection_id_foreign');
            $table->unsignedBigInteger('currency_id')->index('orders_currency_id_foreign');
            $table->unsignedBigInteger('address_invoice_id')->index('orders_address_invoice_id_foreign');
            $table->unsignedBigInteger('address_delivery_id')->nullable()->index('orders_address_delivery_id_foreign');
            $table->unsignedBigInteger('language_id')->nullable()->index('orders_language_id_foreign');
            $table->unsignedBigInteger('order_type_id')->index('orders_order_type_id_foreign');
            $table->unsignedBigInteger('price_list_id')->index('orders_price_list_id_foreign');
            $table->unsignedBigInteger('unit_price_price_list_id')->nullable();
            $table->unsignedBigInteger('delivery_type_id')->nullable();
            $table->unsignedBigInteger('logistics_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->index('orders_payment_type_id_foreign');
            $table->unsignedBigInteger('responsible_user_id')->nullable()->index('orders_responsible_user_id_foreign');
            $table->unsignedBigInteger('tax_exemption_id')->nullable();
            $table->json('address_invoice')->nullable();
            $table->json('address_delivery')->nullable();
            $table->string('state')->nullable();
            $table->string('payment_state')->nullable();
            $table->string('delivery_state')->nullable();
            $table->unsignedInteger('payment_target');
            $table->unsignedInteger('payment_discount_target')->nullable();
            $table->decimal('payment_discount_percent', 40, 10)->nullable();
            $table->decimal('header_discount', 40, 10)->default(0);
            $table->decimal('shipping_costs_net_price', 40, 10)->nullable()->comment('A decimal containing the net price of shipping costs.');
            $table->decimal('shipping_costs_gross_price', 40, 10)->nullable()->comment('A decimal containing the gross price of shipping costs.');
            $table->decimal('shipping_costs_vat_price', 40, 10)->nullable()->comment('A decimal containing the vat price of shipping costs.');
            $table->decimal('shipping_costs_vat_rate_percentage', 40, 10)->nullable()->comment('A decimal, containing the vat-rate in percent for the shipping costs, that is cached for easier and faster readability of this order.');
            $table->decimal('total_base_net_price', 40, 10)->nullable();
            $table->decimal('total_base_gross_price', 40, 10)->nullable();
            $table->decimal('gross_profit', 40, 10)->default(0);
            $table->decimal('total_purchase_price', 40, 10)->default(0);
            $table->decimal('total_cost', 40, 10)->nullable();
            $table->decimal('margin', 40, 10)->default(0);
            $table->decimal('total_net_price', 40, 10)->default(0);
            $table->decimal('total_gross_price', 40, 10)->default(0);
            $table->json('total_vats')->nullable();
            $table->decimal('balance', 40, 10)->nullable();
            $table->unsignedInteger('number_of_packages')->nullable();
            $table->unsignedInteger('payment_reminder_days_1');
            $table->unsignedInteger('payment_reminder_days_2');
            $table->unsignedInteger('payment_reminder_days_3');
            $table->unsignedInteger('payment_reminder_current_level')->nullable();
            $table->date('payment_reminder_next_date')->nullable();
            $table->string('order_number');
            $table->string('commission')->nullable();
            $table->string('iban')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();
            $table->longText('header')->nullable();
            $table->longText('footer')->nullable();
            $table->longText('logistic_note')->nullable();
            $table->string('tracking_email')->nullable();
            $table->json('payment_texts')->nullable();
            $table->date('order_date');
            $table->date('invoice_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('system_delivery_date')->nullable();
            $table->date('system_delivery_date_end')->nullable();
            $table->date('customer_delivery_date')->nullable();
            $table->date('date_of_approval')->nullable();
            $table->boolean('has_logistic_notify_phone_number')->default(false);
            $table->boolean('has_logistic_notify_number')->default(false);
            $table->boolean('is_locked')->default(false)->comment('If set to true this order cant be edited anymore, this happens usually when the invoice was printed.');
            $table->boolean('is_new_customer')->default(false);
            $table->boolean('is_imported')->default(false);
            $table->boolean('is_merge_invoice')->default(false);
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->unique(['order_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
