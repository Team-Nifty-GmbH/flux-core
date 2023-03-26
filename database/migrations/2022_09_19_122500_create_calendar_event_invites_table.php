<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_invites', function (Blueprint $table) {
            $table->foreignId('calendar_event_id')
                ->constrained('calendar_events')
                ->cascadeOnDelete();
            $table->foreignId('model_calendar_id')
                ->nullable()
                ->constrained('calendars')
                ->cascadeOnDelete()
                ->comment('When a model accepts the invite will be shown in the models calendar. ' .
                'This id references to the model calendar where the calendar event is shown.');
            $table->string('status')->index()->nullable();
            $table->morphs('model');
            $table->timestamps();

            $table->primary(
                [
                    'calendar_event_id',
                    'model_type',
                    'model_id',
                ],
                'calendar_event_invites_morphs'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_invites');
    }
};
