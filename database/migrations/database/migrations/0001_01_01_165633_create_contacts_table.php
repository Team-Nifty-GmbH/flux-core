<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contacts')) {
            return;
        }

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('approval_user_id')->nullable()->index('contacts_approval_user_id_foreign');
            $table->unsignedBigInteger('payment_type_id')->nullable()->index('contacts_payment_type_id_foreign');
            $table->unsignedBigInteger('purchase_payment_type_id')->nullable()->index('contacts_purchase_payment_type_id_foreign');
            $table->unsignedBigInteger('price_list_id')->nullable();
            $table->unsignedBigInteger('client_id')->index('contacts_client_id_foreign');
            $table->unsignedBigInteger('agent_id')->nullable()->index('contacts_agent_id_foreign');
            $table->unsignedBigInteger('contact_origin_id')->nullable()->index('contacts_contact_origin_id_foreign');
            $table->unsignedBigInteger('currency_id')->nullable()->index('contacts_currency_id_foreign');
            $table->unsignedBigInteger('expense_ledger_account_id')->nullable()->index('contacts_expense_ledger_account_id_foreign');
            $table->unsignedBigInteger('main_address_id')->nullable()->index('contacts_main_address_id_foreign');
            $table->unsignedBigInteger('invoice_address_id')->nullable()->index('contacts_invoice_address_id_foreign');
            $table->unsignedBigInteger('delivery_address_id')->nullable()->index('contacts_delivery_address_id_foreign');
            $table->unsignedBigInteger('vat_rate_id')->nullable()->index('contacts_vat_rate_id_foreign');
            $table->string('customer_number');
            $table->string('creditor_number')->nullable();
            $table->string('debtor_number')->nullable();
            $table->integer('payment_target_days')->nullable();
            $table->integer('payment_reminder_days_1')->nullable();
            $table->integer('payment_reminder_days_2')->nullable();
            $table->integer('payment_reminder_days_3')->nullable();
            $table->integer('discount_days')->nullable();
            $table->double('discount_percent')->nullable();
            $table->decimal('credit_line')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('vendor_customer_number')->nullable();
            $table->boolean('has_sensitive_reminder')->default(false);
            $table->boolean('has_delivery_lock')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->unique(['creditor_number', 'client_id']);
            $table->unique(['customer_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
