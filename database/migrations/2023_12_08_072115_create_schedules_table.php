<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->string('class');
            $table->string('type');
            $table->text('description')->nullable();
            $table->json('cron');
            $table->json('parameters')->nullable();
            $table->string('cron_expression')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('recurrences')->nullable();
            $table->unsignedInteger('current_recurrence')->nullable();
            $table->dateTime('last_success')->nullable();
            $table->dateTime('last_run')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('schedules');
    }
};
