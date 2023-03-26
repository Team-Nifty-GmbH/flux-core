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
        Schema::table('calendar_event_invites', function (Blueprint $table) {
            $table->dropForeign(['calendar_event_id']);
            $table->dropPrimary(['calendar_event_id', 'model_type', 'model_id']);
        });

        Schema::table('calendar_event_invites', function (Blueprint $table) {
            $table->id()->first();
            $table->foreign('calendar_event_id')
                ->references('id')
                ->on('calendar_events')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_event_invites', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->dropColumn('id');
        });

        Schema::table('calendar_event_invites', function (Blueprint $table) {
            $table->primary(['calendar_event_id', 'model_type', 'model_id'], 'calendar_event_invites_morphs');
        });
    }
};
