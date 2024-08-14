<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('record_histories');
    }

    public function down(): void
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
