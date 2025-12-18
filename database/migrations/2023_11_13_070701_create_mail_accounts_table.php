<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_accounts', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->string('protocol')->nullable()->default('imap');
            $table->string('email')->nullable()->unique();
            $table->text('password')->nullable();
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption')->nullable();

            $table->string('smtp_mailer')->default('smtp');
            $table->string('smtp_email')->nullable();
            $table->string('smtp_from_name')->nullable();
            $table->string('smtp_reply_to')->nullable();
            $table->string('smtp_user')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable();

            $table->boolean('has_auto_assign')->default(false);
            $table->boolean('has_o_auth')->default(false);
            $table->boolean('has_valid_certificate')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
    }
};
