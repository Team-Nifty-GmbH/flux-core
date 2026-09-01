<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('payment_run_positions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('payment_run_id')
                ->constrained('payment_runs')
                ->cascadeOnDelete();
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('account_holder')->nullable();
            $table->decimal('amount', 40, 10);
            $table->string('purpose')->nullable();
            $table->string('end_to_end_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->unique(['payment_run_id', 'end_to_end_id']);
            $table->index('iban');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_run_positions');
    }
};
