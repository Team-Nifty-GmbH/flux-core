<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign(['address_delivery_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['address_invoice_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['agent_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['approval_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['contact_bank_connection_id'])->references(['id'])->on('contact_bank_connections')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['language_id'])->references(['id'])->on('languages')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['order_type_id'])->references(['id'])->on('order_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['parent_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['responsible_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_address_delivery_id_foreign');
            $table->dropForeign('orders_address_invoice_id_foreign');
            $table->dropForeign('orders_agent_id_foreign');
            $table->dropForeign('orders_approval_user_id_foreign');
            $table->dropForeign('orders_client_id_foreign');
            $table->dropForeign('orders_contact_bank_connection_id_foreign');
            $table->dropForeign('orders_contact_id_foreign');
            $table->dropForeign('orders_currency_id_foreign');
            $table->dropForeign('orders_language_id_foreign');
            $table->dropForeign('orders_order_type_id_foreign');
            $table->dropForeign('orders_parent_id_foreign');
            $table->dropForeign('orders_payment_type_id_foreign');
            $table->dropForeign('orders_price_list_id_foreign');
            $table->dropForeign('orders_responsible_user_id_foreign');
        });
    }
};
