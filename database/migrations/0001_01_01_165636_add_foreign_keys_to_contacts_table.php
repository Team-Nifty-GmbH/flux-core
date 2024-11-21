<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign(['agent_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['approval_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['contact_origin_id'])->references(['id'])->on('contact_origins')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['delivery_address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['expense_ledger_account_id'])->references(['id'])->on('ledger_accounts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['invoice_address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['main_address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['purchase_payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['vat_rate_id'])->references(['id'])->on('vat_rates')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_agent_id_foreign');
            $table->dropForeign('contacts_approval_user_id_foreign');
            $table->dropForeign('contacts_client_id_foreign');
            $table->dropForeign('contacts_contact_origin_id_foreign');
            $table->dropForeign('contacts_currency_id_foreign');
            $table->dropForeign('contacts_delivery_address_id_foreign');
            $table->dropForeign('contacts_expense_ledger_account_id_foreign');
            $table->dropForeign('contacts_invoice_address_id_foreign');
            $table->dropForeign('contacts_main_address_id_foreign');
            $table->dropForeign('contacts_payment_type_id_foreign');
            $table->dropForeign('contacts_purchase_payment_type_id_foreign');
            $table->dropForeign('contacts_vat_rate_id_foreign');
        });
    }
};
