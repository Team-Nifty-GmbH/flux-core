<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
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
            $table->dateTime('last_success')->nullable();
            $table->dateTime('last_run')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
