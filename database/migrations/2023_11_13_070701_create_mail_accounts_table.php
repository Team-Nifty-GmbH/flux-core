<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('protocol')->default('imap');
            $table->string('email')->unique();
            $table->text('password');
            $table->string('host');
            $table->integer('port')->default(993);
            $table->string('encryption')->default('ssl');
            $table->boolean('is_o_auth')->default(false);
            $table->boolean('has_valid_certificate')->default(true);

            $table->string('smtp_mailer')->default('smtp');
            $table->string('smtp_email')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
    }
};
