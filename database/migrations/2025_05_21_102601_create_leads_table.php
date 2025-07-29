<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('lead_loss_reason_id')
                ->nullable()
                ->constrained('lead_loss_reasons')
                ->nullOnDelete();
            $table->foreignId('lead_state_id')
                ->nullable()
                ->constrained('lead_states')
                ->nullOnDelete();
            $table->foreignId('recommended_by_address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();
            $table->foreignId('record_origin_id')
                ->nullable()
                ->constrained('record_origins')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->text('loss_reason')->nullable();
            $table->date('start');
            $table->date('end');
            $table->decimal('probability_percentage', 11, 10)->default(0);
            $table->decimal('expected_revenue', 40, 10)->nullable();
            $table->decimal('expected_gross_profit', 40, 10)->nullable();
            $table->decimal('expected_gross_profit_percentage', 11, 10)->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->decimal('weighted_gross_profit', 40, 10)->nullable();
            $table->decimal('weighted_revenue', 40, 10)->nullable();

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
        Schema::dropIfExists('leads');
    }
};
