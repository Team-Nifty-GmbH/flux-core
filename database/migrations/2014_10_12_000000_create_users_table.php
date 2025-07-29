<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies');
                $table->foreignId('language_id')->constrained('languages');
                $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email')->unique();
                $table->string('firstname');
                $table->string('lastname');
                $table->string('name');
                $table->string('phone')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('user_code')->nullable()->unique();
                $table->string('timezone')->nullable();
                $table->string('color')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('employee_number')->nullable();
                $table->date('employment_date')->nullable();
                $table->date('termination_date')->nullable();
                $table->string('iban')->nullable();
                $table->string('account_holder')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bic')->nullable();
                $table->decimal('cost_per_hour', 10)->nullable();
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
                $table->rememberToken();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_dark_mode')->default(false);
                $table->timestamp('created_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
