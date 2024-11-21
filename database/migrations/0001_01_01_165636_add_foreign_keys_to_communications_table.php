<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->foreign(['mail_account_id'], 'mail_messages_mail_account_id_foreign')->references(['id'])->on('mail_accounts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mail_folder_id'], 'mail_messages_mail_folder_id_foreign')->references(['id'])->on('mail_folders')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dropForeign('mail_messages_mail_account_id_foreign');
            $table->dropForeign('mail_messages_mail_folder_id_foreign');
        });
    }
};
