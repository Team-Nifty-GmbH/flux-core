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
        Schema::dropIfExists('record_histories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('record_histories', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->morphs('model');
            $table->json('data');
            $table->timestamps();
        });
    }
};
