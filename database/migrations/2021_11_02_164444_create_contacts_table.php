<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contacts')) {
            return;
        }

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('approval_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();
            $table->foreignId('expense_ledger_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();
            $table->foreignId('payment_type_id')
                ->nullable()
                ->constrained('payment_types')
                ->nullOnDelete();
            $table->foreignId('price_list_id')
                ->nullable()
                ->constrained('price_lists')
                ->nullOnDelete();
            $table->foreignId('purchase_payment_type_id')
                ->nullable()
                ->constrained('payment_types')
                ->nullOnDelete();
            $table->foreignId('record_origin_id')
                ->nullable()
                ->constrained('record_origins')
                ->nullOnDelete();
            $table->foreignId('vat_rate_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();

            $table->string('customer_number');
            $table->string('creditor_number')->nullable();
            $table->string('debtor_number')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->integer('payment_target_days')->nullable();
            $table->integer('payment_reminder_days_1')->nullable();
            $table->integer('payment_reminder_days_2')->nullable();
            $table->integer('payment_reminder_days_3')->nullable();
            $table->integer('discount_days')->nullable();
            $table->double('discount_percent')->nullable();
            $table->decimal('credit_line')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('customs_identifier')->nullable();
            $table->string('vendor_customer_number')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->boolean('has_delivery_lock')->default(false);
            $table->boolean('has_sensitive_reminder')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['customer_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
