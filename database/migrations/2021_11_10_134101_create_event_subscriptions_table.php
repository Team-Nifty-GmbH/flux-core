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
            $table->string('event')->index();
            $table->morphs('subscribable');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->boolean('is_broadcast')->default(false);
            $table->boolean('is_notifiable')->default(false);
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};
