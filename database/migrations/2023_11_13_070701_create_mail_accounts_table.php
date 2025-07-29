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
            $table->string('protocol')->default('imap');
            $table->string('email')->unique();
            $table->text('password');
            $table->string('host');
            $table->integer('port')->default(993);
            $table->string('encryption')->default('ssl');

            $table->string('smtp_mailer')->default('smtp');
            $table->string('smtp_email')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->nullable();

            $table->boolean('has_valid_certificate')->default(true);
            $table->boolean('is_auto_assign')->default(false);
            $table->boolean('is_o_auth')->default(false);
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
