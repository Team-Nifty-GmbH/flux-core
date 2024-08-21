<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            //id
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('order_type_id');
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('delivery_type_id')->nullable();
            $table->unsignedBigInteger('logistics_id')->nullable();
            $table->unsignedBigInteger('payment_type_id');
            $table->unsignedBigInteger('tax_exemption_id')->nullable();

            //number
            $table->integer('payment_target', false, true);
            $table->integer('payment_discount_target', false, true)->nullable();
            $table->decimal('payment_discount_percent', 40, 10, true)->nullable();
            $table->decimal('sum_order_position_net', 40, 10)->default(0);
            $table->decimal('sum_order_position_gross', 40, 10)->default(0);
            $table->decimal('header_discount', 40, 10, true)->default(0);
            $table->decimal('shipping_costs', 40, 10)->default(0);
            $table->decimal('total_price_gross', 40, 10)->default(0);
            $table->decimal('total_price_net', 40, 10)->default(0);
            $table->decimal('margin', 40, 10)->default(0);
            $table->integer('number_of_packages', false, true)->nullable();
            $table->integer('payment_reminder_days_1', false, true);
            $table->integer('payment_reminder_days_2', false, true);
            $table->integer('payment_reminder_days_3', false, true);

            //string
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->text('logistic_note')->nullable();
            $table->string('tracking_email')->nullable();
            $table->json('vats');
            $table->json('payment_texts')->nullable();

            //date
            $table->date('order_date');
            $table->date('invoice_date')->nullable();
            $table->date('system_delivery_date')->nullable();
            $table->date('customer_delivery_date')->nullable();
            $table->date('date_of_approval')->nullable();

            //boolean
            $table->boolean('is_locked')->default(false);
            $table->boolean('has_logistic_notify_phone_number')->default(false);
            $table->boolean('has_logistic_notify_number')->default(false);
            $table->boolean('is_new_customer')->default(false);
            $table->boolean('is_imported')->default(false);
            $table->boolean('is_merge_invoice')->default(false);
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('requires_approval')->default(false);

            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->timestamp('deleted_at')->nullable()
                ->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.');

            // foreign
            $table->foreign('parent_id')->references('id')->on('orders');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('order_type_id')->references('id')->on('order_types');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
