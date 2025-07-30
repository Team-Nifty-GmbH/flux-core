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
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->char('uuid', 36)->unique()->after('id');
            $table->foreignId('contact_id')
                ->nullable()
                ->after('uuid')
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('currency_id')
                ->nullable()
                ->after('contact_id')
                ->constrained('currencies');
            $table->foreignId('language_id')
                ->after('currency_id')
                ->constrained('languages');
            $table->foreignId('parent_id')
                ->nullable()
                ->after('language_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('firstname')->after('parent_id');
            $table->string('lastname')->after('firstname');
            $table->string('phone')->nullable()->after('name');
            $table->string('user_code')->nullable()->unique()->after('phone');
            $table->string('timezone')->nullable()->after('password');
            $table->string('color')->nullable()->after('timezone');
            $table->date('date_of_birth')->nullable()->after('color');
            $table->string('employee_number')->nullable()->after('date_of_birth');
            $table->date('employment_date')->nullable()->after('employee_number');
            $table->date('termination_date')->nullable()->after('employment_date');
            $table->string('iban')->nullable()->after('termination_date');
            $table->string('account_holder')->nullable()->after('iban');
            $table->string('bank_name')->nullable()->after('account_holder');
            $table->string('bic')->nullable()->after('bank_name');
            $table->decimal('cost_per_hour', 10)->nullable()->after('bic');
            $table->boolean('is_active')->default(true)->after('remember_token');
            $table->boolean('is_dark_mode')->default(false)->after('is_active');
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->timestamp('deleted_at')->nullable()->after('updated_by');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
