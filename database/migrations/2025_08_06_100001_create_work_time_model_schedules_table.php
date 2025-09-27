<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('work_time_model_schedules', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->foreignId('work_time_model_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('week_number');
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('break_minutes', 5, 2)->default(0);
            $table->decimal('work_hours', 4, 2)->default(0);

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
        Schema::dropIfExists('work_time_model_schedules');
    }
};
