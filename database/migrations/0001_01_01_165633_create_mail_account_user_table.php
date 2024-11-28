<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_account_user')) {
            return;
        }

        Schema::create('mail_account_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('mail_account_user_user_id_foreign');
            $table->unsignedBigInteger('mail_account_id')->index('mail_account_user_mail_account_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_account_user');
    }
};
