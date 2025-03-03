<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->boolean('has_taken_place')->default(false)->after('is_all_day');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('has_taken_place');
        });
    }
};
