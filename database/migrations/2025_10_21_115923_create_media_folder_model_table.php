<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('media_folder_model', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('media_folder_id')->constrained('media_folders')->cascadeOnDelete();
            $table->morphs('model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_folder_model');
    }
};
