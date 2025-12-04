<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('key');
            $table->nullableMorphs('model');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['model_id', 'model_type', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
