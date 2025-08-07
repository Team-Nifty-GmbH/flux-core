<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_types', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->string('color', 7);
            $table->boolean('is_active')->default(true);
            $table->boolean('can_select_substitute')->default(false);
            $table->boolean('must_select_substitute')->default(false);
            $table->boolean('requires_proof')->default(false);
            $table->boolean('requires_reason')->default(false);
            $table->enum('employee_can_create', ['yes', 'no', 'approval_required'])->default('yes');
            $table->boolean('counts_as_work_day')->default(true);
            $table->boolean('counts_as_target_hours')->default(true);
            $table->boolean('requires_work_day')->default(false);
            $table->boolean('is_vacation')->default(false);
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
            
            $table->unique(['uuid']);
            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_types');
    }
};