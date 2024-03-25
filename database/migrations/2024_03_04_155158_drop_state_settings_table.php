<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('state_settings');
    }

    public function down(): void
    {
        Schema::create('state_settings', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('model');
            $table->string('color');
            $table->timestamps();
        });
    }
};
