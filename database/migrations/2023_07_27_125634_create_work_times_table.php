<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('work_times', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->cascadeOnDelete();
            $table->foreignId('order_position_id')
                ->nullable()
                ->unique()
                ->constrained('order_positions')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('work_times')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('work_time_type_id')->nullable();
            $table->nullableMorphs('trackable');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedBigInteger('paused_time_ms')->default(0);
            $table->bigInteger('total_time_ms')->default(0);
            $table->decimal('total_cost', 10)->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_daily_work_time')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_pause')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('work_time_type_id')->references('id')->on('work_time_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_times');
    }
};
