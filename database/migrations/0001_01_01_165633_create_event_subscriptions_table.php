<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_subscriptions')) {
            return;
        }

        Schema::create('event_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('event')->index('event_notifications_event_index');
            $table->unsignedBigInteger('subscribable_id');
            $table->string('subscribable_type');
            $table->string('model_type')->index('event_notifications_model_type_index');
            $table->unsignedBigInteger('model_id')->nullable()->index('event_notifications_model_id_index');
            $table->boolean('is_broadcast')->default(false);
            $table->boolean('is_notifiable')->default(false);
            $table->timestamps();

            $table->index(['subscribable_id', 'subscribable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};
