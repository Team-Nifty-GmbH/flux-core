<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->date('date')->nullable();
            $table->unsignedInteger('month')->nullable();
            $table->unsignedInteger('day')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->year('effective_from')->nullable();
            $table->year('effective_until')->nullable();
            $table->enum('day_part', ['full', 'first_half', 'second_half'])->default('full');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
            
            $table->unique(['uuid']);
            $table->index(['client_id', 'date']);
            $table->index(['client_id', 'month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};