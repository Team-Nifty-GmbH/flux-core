<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('model_type')->nullable()->after('calendar_id');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropMorphs('model');
        });
    }
};
