<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('locks');
    }

    public function down(): void
    {
        Schema::create('locks', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');
            $table->morphs('authenticatable');
            $table->timestamp('created_at');
            $table->string('created_by');
            $table->timestamp('updated_at');
            $table->string('updated_by');

            $table->unique(['model_type', 'model_id']);
        });
    }
};
