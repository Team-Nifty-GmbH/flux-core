<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            return;
        }

        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('client_id')->constrained('clients');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
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
            $table->string('phone_mobile')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('password')->nullable();
            $table->json('search_aliases')->nullable();
            $table->string('advertising_state')->default('open');
            $table->boolean('can_login')->default(false);
            $table->boolean('has_formal_salutation')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_dark_mode')->default(false);
            $table->boolean('is_delivery_address')->default(false);
            $table->boolean('is_invoice_address')->default(false);
            $table->boolean('is_main_address')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('language_id')->references('id')->on('languages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
