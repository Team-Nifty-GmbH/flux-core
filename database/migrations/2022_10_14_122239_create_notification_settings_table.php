<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('notifiable');
            $table->string('notification_type')->index();
            $table->string('channel')->index();
            $table->json('channel_value')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(
                ['notifiable_id', 'notifiable_type', 'notification_type', 'channel'],
                'notification_settings_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
