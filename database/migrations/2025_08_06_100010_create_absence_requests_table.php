<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('absence_type_id')->constrained('absence_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('start_half_day', ['full', 'first_half', 'second_half'])->default('full');
            $table->enum('end_half_day', ['full', 'first_half', 'second_half'])->default('full');
            $table->decimal('days_requested', 5, 2);
            $table->text('reason')->nullable();
            $table->text('substitute_note')->nullable();
            $table->foreignId('substitute_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
            
            $table->unique(['uuid']);
            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['approved_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};