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
        Schema::dropIfExists('model_has_external_record');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('model_has_external_record', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->morphs('model');
            $table->unsignedBigInteger('setting_id');
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->foreign('setting_id')->references('id')->on('settings');
        });
    }
};
