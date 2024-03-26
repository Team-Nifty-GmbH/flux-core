<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_data', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->nullableMorphs('model');
            $table->json('data')->nullable();
            $table->string('view');
            $table->string('template_name')->nullable();
            $table->string('request_hash')->unique()->index();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_data');
    }
}
