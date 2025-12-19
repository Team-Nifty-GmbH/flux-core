<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex('addresses_login_name_index');
            $table->index(['email']);
        });

        // Categorizable
        Schema::table('categorizable', function (Blueprint $table): void {
            $table->index(['categorizable_type', 'categorizable_id']);
        });

        // Comments
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')->references('id')->on('comments')->nullOnDelete();
        });

        // Communicatable
        Schema::table('communicatable', function (Blueprint $table): void {
            $table->index(['communicatable_type', 'communicatable_id']);
        });

        // Communications
        Schema::table('communications', function (Blueprint $table): void {
            $table->dropForeign('mail_messages_mail_account_id_foreign');
            $table->dropForeign('mail_messages_mail_folder_id_foreign');
            $table->dropIndex('mail_messages_message_id_index');
            $table->dropIndex('mail_messages_message_uid_index');
            $table->foreign('mail_account_id')->references('id')->on('mail_accounts')->nullOnDelete();
            $table->foreign('mail_folder_id')->references('id')->on('mail_folders')->nullOnDelete();
            $table->index(['message_id']);
            $table->index(['message_uid']);
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['payment_type_id']);
            $table->foreign('payment_type_id')->references('id')->on('payment_types')->nullOnDelete();
        });

        // PurchaseInvoices
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropForeign(['lay_out_user_id']);
            $table->foreign('lay_out_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Addresses
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex('addresses_email_index');
            $table->index(['email'], 'addresses_login_name_index');
        });

        // Categorizable
        Schema::table('categorizable', function (Blueprint $table): void {
            $table->dropIndex('categorizable_categorizable_type_categorizable_id_index');
        });

        // Comments
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')->references('id')->on('comments');
        });

        // Communicatable
        Schema::table('communicatable', function (Blueprint $table): void {
            $table->dropIndex('communicatable_communicatable_type_communicatable_id_index');
        });

        // Communications
        Schema::table('communications', function (Blueprint $table): void {
            $table->dropForeign('communications_mail_account_id_foreign');
            $table->dropForeign('communications_mail_folder_id_foreign');
            $table->dropIndex('communications_message_id_index');
            $table->dropIndex('communications_message_uid_index');
            $table->foreign('mail_account_id', 'mail_messages_mail_account_id_foreign')
                ->references('id')
                ->on('mail_accounts')
                ->nullOnDelete();
            $table->foreign('mail_folder_id', 'mail_messages_mail_folder_id_foreign')
                ->references('id')
                ->on('mail_folders')
                ->nullOnDelete();
            $table->index(['message_id'], 'mail_messages_message_id_index');
            $table->index(['message_uid'], 'mail_messages_message_uid_index');
        });

        // Contacts
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['payment_type_id']);
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
        });

        // PurchaseInvoices
        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropForeign(['lay_out_user_id']);
            $table->foreign('lay_out_user_id')->references('id')->on('users');
        });
    }
};
