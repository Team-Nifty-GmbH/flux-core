<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('payment_runs', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('bank_connection_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('state')->default('open');
            $table->string('payment_run_type_enum');
            $table->string('sepa_mandate_type_enum')->nullable();
            $table->date('instructed_execution_date')->nullable();
            $table->boolean('is_single_booking')->default(true);
            $table->boolean('is_instant_payment')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_runs');
    }
};
