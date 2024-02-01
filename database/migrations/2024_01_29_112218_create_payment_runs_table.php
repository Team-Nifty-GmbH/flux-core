<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('bank_connection_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('state')->default('open');
            $table->string('payment_run_type_enum');
            $table->date('instructed_execution_date')->nullable();
            $table->boolean('is_single_booking')->default(false);
            $table->boolean('is_instant_payment')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_runs');
    }
};
