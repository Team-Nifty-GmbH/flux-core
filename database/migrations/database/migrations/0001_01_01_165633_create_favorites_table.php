<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('favorites')) {
            return;
        }

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('authenticatable_type');
            $table->unsignedBigInteger('authenticatable_id');
            $table->string('name');
            $table->string('url');
            $table->timestamps();

            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
