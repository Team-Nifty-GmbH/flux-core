<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table): void {
                $table->id();
                $table->char('uuid', 36);
                $table->foreignId('order_position_id')
                    ->nullable()
                    ->constrained('order_positions')
                    ->nullOnDelete();
                $table->foreignId('project_id')
                    ->nullable()
                    ->constrained('projects')
                    ->nullOnDelete();
                $table->foreignId('responsible_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->nullableMorphs('model');
                $table->string('name');
                $table->text('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('due_date')->nullable();
                $table->unsignedInteger('priority')->default(0);
                $table->string('state')->default('open');
                $table->decimal('progress', 11, 10)->default(0);
                $table->unsignedBigInteger('time_budget')->nullable()
                    ->comment('Time budget in minutes.');
                $table->decimal('budget', 40, 10)->nullable();
                $table->decimal('total_cost', 10)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
