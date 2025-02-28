<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('inviteables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_event_id');
            $table->unsignedBigInteger('model_calendar_id')->nullable();
            $table->morphs('inviteable');
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('calendar_event_id')
                ->references('id')
                ->on('calendar_events')
                ->onDelete('cascade');
            $table->foreign('model_calendar_id')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');

            $table->unique(['calendar_event_id', 'inviteable_id', 'inviteable_type'], 'inviteables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inviteables');
    }
};
