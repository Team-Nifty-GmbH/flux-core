<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_account_user', function (Blueprint $table) {
            $table->foreign(['mail_account_id'])->references(['id'])->on('mail_accounts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('mail_account_user', function (Blueprint $table) {
            $table->dropForeign('mail_account_user_mail_account_id_foreign');
            $table->dropForeign('mail_account_user_user_id_foreign');
        });
    }
};
