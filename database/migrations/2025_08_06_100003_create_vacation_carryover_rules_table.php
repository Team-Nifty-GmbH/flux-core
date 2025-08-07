<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_carryover_rules', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->year('effective_year');
            $table->unsignedInteger('cutoff_month');
            $table->unsignedInteger('cutoff_day');
            $table->unsignedInteger('max_carryover_days')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
            
            $table->unique(['uuid']);
            $table->index(['client_id', 'effective_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_carryover_rules');
    }
};