<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendarable')) {
            return;
        }

        Schema::create('calendarable', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('calendar_id')->constrained('calendars')->cascadeOnDelete();
            $table->morphs('calendarable');
            $table->string('permission')->default('owner');

            $table->unique(['calendar_id', 'calendarable_id', 'calendarable_type'], 'calendarable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendarable');
    }
};
