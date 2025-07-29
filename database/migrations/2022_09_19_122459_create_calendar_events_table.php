<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->ulid();
            $table->foreignId('calendar_id')->constrained('calendars')->cascadeOnDelete();
            $table->nullableMorphs('model');
            $table->dateTimeTz('start');
            $table->dateTimeTz('end')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('repeat')->nullable();
            $table->dateTime('repeat_end')->nullable();
            $table->unsignedInteger('recurrences')->nullable();
            $table->json('cancelled')->nullable();
            $table->json('excluded')->nullable();
            $table->json('extended_props')->nullable();
            $table->boolean('has_taken_place')->default(false);
            $table->boolean('is_all_day')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
