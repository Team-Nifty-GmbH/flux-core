<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_bookings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('credit_ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();
            $table->foreignId('debit_ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->nullableMorphs('source');
            $table->decimal('amount', 40, 10);
            $table->date('booking_date');
            $table->string('booking_text')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('ledger_bookings');
    }
};
