<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('event_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('event')->index();
            $table->unsignedBigInteger('user_id');
            $table->string('model_type')->index();
            $table->unsignedBigInteger('model_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_notifications');
    }
}
