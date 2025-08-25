<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('foreign_uuid')->nullable()->index();
            $table->longText('message');
            $table->longText('context');
            $table->string('level')->index();
            $table->string('level_name');
            $table->string('channel');
            $table->dateTime('record_datetime');
            $table->longText('extra');
            $table->longText('formatted');
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
