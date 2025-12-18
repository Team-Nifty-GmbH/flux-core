<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('event_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('subscribable');
            $table->string('channel')->index();
            $table->string('event')->index();
            $table->boolean('is_broadcast')->default(false);
            $table->boolean('is_notifiable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};
