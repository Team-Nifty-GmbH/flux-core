<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordHistoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('record_histories', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->morphs('model');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_histories');
    }
}
