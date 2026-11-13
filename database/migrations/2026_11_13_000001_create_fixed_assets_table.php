<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('depreciation_ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();
            $table->foreignId('ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('order_position_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('number')->nullable();
            $table->date('acquired_at');
            $table->date('depreciation_starts_at');
            $table->decimal('acquisition_cost', 40, 10);
            $table->decimal('residual_value', 40, 10)->default(0);
            $table->decimal('book_value', 40, 10)->default(0);
            $table->string('depreciation_method_enum');
            $table->unsignedInteger('useful_life_months');
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
        Schema::dropIfExists('fixed_assets');
    }
};
