<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            return;
        }

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('addresses_client_id_foreign')->comment('A unique identifier number for the table clients.');
            $table->unsignedBigInteger('language_id')->nullable()->index('addresses_language_id_foreign');
            $table->unsignedBigInteger('country_id')->nullable()->index('addresses_country_id_foreign');
            $table->unsignedBigInteger('contact_id')->index('addresses_contact_id_foreign');
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->string('salutation')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('name')->nullable();
            $table->string('addition')->nullable();
            $table->string('mailbox')->nullable();
            $table->string('mailbox_city')->nullable();
            $table->string('mailbox_zip')->nullable();
            $table->decimal('latitude', 15, 12)->nullable();
            $table->decimal('longitude', 15, 12)->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('url')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable()->index('addresses_login_name_index');
            $table->string('password')->nullable();
            $table->boolean('has_formal_salutation')->default(true);
            $table->boolean('is_main_address')->default(false);
            $table->boolean('is_invoice_address')->default(false);
            $table->boolean('is_dark_mode')->default(false);
            $table->boolean('is_delivery_address')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('can_login')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
