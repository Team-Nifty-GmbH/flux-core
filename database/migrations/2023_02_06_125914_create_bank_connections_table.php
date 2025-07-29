<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bank_connections')) {
            Schema::create('bank_connections', function (Blueprint $table): void {
                $table->id();
                $table->char('uuid', 36);
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->foreignId('ledger_account_id')
                    ->nullable()
                    ->constrained('ledger_accounts')
                    ->nullOnDelete();
                $table->string('name')->nullable();
                $table->string('account_holder')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('iban')->nullable()->unique();
                $table->string('bic')->nullable();
                $table->integer('credit_limit')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_virtual')->default(false);
                $table->timestamp('created_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by')->nullable();

                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_connections');
    }
};
