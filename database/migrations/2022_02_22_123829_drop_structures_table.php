<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropStructuresTable extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('structures');
    }

    public function down(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('model')->index();
            $table->string('type')->nullable();
            $table->json('structure');
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('structures');
        });
    }
}
