<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_invoices')) {
            return;
        }

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('approval_user_id')->nullable()->index('purchase_invoices_approval_user_id_foreign');
            $table->unsignedBigInteger('client_id')->nullable()->index('purchase_invoices_client_id_foreign');
            $table->unsignedBigInteger('contact_id')->nullable()->index('purchase_invoices_contact_id_foreign');
            $table->unsignedBigInteger('currency_id')->nullable()->index('purchase_invoices_currency_id_foreign');
            $table->unsignedBigInteger('lay_out_user_id')->nullable()->index('purchase_invoices_lay_out_user_id_foreign');
            $table->unsignedBigInteger('media_id')->nullable()->index('purchase_invoices_media_id_foreign');
            $table->unsignedBigInteger('order_id')->nullable()->index('purchase_invoices_order_id_foreign');
            $table->unsignedBigInteger('order_type_id')->nullable()->index('purchase_invoices_order_type_id_foreign');
            $table->unsignedBigInteger('payment_type_id')->nullable()->index('purchase_invoices_payment_type_id_foreign');
            $table->date('invoice_date');
            $table->date('system_delivery_date')->nullable();
            $table->date('system_delivery_date_end')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('hash')->unique();
            $table->string('iban')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();
            $table->boolean('is_net')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
