<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('model_has_external_record');
    }

    public function down(): void
    {
        Schema::create('model_has_external_record', function (Blueprint $table): void {
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
