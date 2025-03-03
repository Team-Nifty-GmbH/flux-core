<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->morphs('calendarable');
            $table->unsignedBigInteger('calendar_id')->nullable();
            $table->string('name');
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('calendar_groups')
                ->onDelete('cascade');
            $table->foreign('calendar_id')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_groups');
    }
};
