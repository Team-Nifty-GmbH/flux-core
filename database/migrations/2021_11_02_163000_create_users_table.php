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
            if (! Schema::hasColumn('users', 'uuid')) {
                $table->char('uuid', 36)->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'currency_id')) {
                $table->foreignId('currency_id')
                    ->nullable()
                    ->after('uuid')
                    ->constrained('currencies')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'language_id')) {
                $table->foreignId('language_id')
                    ->after('currency_id')
                    ->constrained('languages');
            }
            if (! Schema::hasColumn('users', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('language_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->after('parent_id');
            }
            if (! Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->after('firstname');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code')->nullable()->unique()->after('phone');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'color')) {
                $table->string('color')->nullable()->after('timezone');
            }
            if (! Schema::hasColumn('users', 'iban')) {
                $table->string('iban')->nullable()->after('color');
            }
            if (! Schema::hasColumn('users', 'account_holder')) {
                $table->string('account_holder')->nullable()->after('iban');
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('account_holder');
            }
            if (! Schema::hasColumn('users', 'bic')) {
                $table->string('bic')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('users', 'cost_per_hour')) {
                $table->decimal('cost_per_hour', 10)->nullable()->after('bic');
            }
            if (! Schema::hasColumn('users', 'has_dark_mode')) {
                $table->boolean('has_dark_mode')->default(false)->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('has_dark_mode');
            }
            if (! Schema::hasColumn('users', 'created_by')) {
                $table->string('created_by')->nullable()->after('created_at');
            }
            if (! Schema::hasColumn('users', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('updated_at');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_by');
            }
            if (! Schema::hasColumn('users', 'deleted_by')) {
                $table->string('deleted_by')->nullable()->after('deleted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
