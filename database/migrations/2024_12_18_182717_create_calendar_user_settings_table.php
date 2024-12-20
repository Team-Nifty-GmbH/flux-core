<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_user_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('authenticatable', 'calendar_authenticatable');
            $table->string('cache_key')->index();
            $table->string('component')->index();
            $table->json('settings');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_user_settings');
    }
};
