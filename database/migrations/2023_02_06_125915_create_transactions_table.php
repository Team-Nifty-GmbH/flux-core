<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('bank_connection_id')
                ->nullable()
                ->constrained('bank_connections')
                ->nullOnDelete();
            $table->foreignId('contact_bank_connection_id')
                ->nullable()
                ->constrained('contact_bank_connections')
                ->nullOnDelete();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('parent_id')->nullable()->constrained('transactions');
            $table->date('value_date');
            $table->date('booking_date');
            $table->decimal('amount', 40, 10);
            $table->decimal('balance', 40, 10)->nullable();
            $table->string('purpose')->nullable();
            $table->string('type')->nullable();
            $table->string('counterpart_name')->nullable();
            $table->string('counterpart_account_number')->nullable();
            $table->string('counterpart_iban')->nullable();
            $table->string('counterpart_bic')->nullable();
            $table->string('counterpart_bank_name')->nullable();
            $table->boolean('is_ignored')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
