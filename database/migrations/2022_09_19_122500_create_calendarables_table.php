<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('calendarables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->morphs('calendarable');
            $table->string('permission')->default('owner');
            $table->timestamps();

            $table->foreign('calendar_id')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');

            $table->unique(['calendar_id', 'calendarable_id', 'calendarable_type'], 'calendarable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendarables');
    }
};
