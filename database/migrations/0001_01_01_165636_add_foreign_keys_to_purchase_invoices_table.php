<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->foreign(['approval_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lay_out_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['media_id'])->references(['id'])->on('media')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_type_id'])->references(['id'])->on('order_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign('purchase_invoices_approval_user_id_foreign');
            $table->dropForeign('purchase_invoices_client_id_foreign');
            $table->dropForeign('purchase_invoices_contact_id_foreign');
            $table->dropForeign('purchase_invoices_currency_id_foreign');
            $table->dropForeign('purchase_invoices_lay_out_user_id_foreign');
            $table->dropForeign('purchase_invoices_media_id_foreign');
            $table->dropForeign('purchase_invoices_order_id_foreign');
            $table->dropForeign('purchase_invoices_order_type_id_foreign');
            $table->dropForeign('purchase_invoices_payment_type_id_foreign');
        });
    }
};
