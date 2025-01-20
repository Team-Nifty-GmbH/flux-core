<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->string('channel')->after('subscribable_type');

            $table->index(['channel']);

            $table->dropColumn(['event', 'model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['channel']);

            $table->string('model_type')->after('subscribable_type');
            $table->unsignedBigInteger('model_id')->after('model_type');

            $table->string('event')->after('subscribable_id');

            $table->index(['model_type', 'model_id']);
        });
    }
};
