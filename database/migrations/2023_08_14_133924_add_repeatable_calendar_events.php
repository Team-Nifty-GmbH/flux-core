<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dateTime('repeat_end')->nullable()->after('repeat');
            $table->unsignedInteger('recurrences')->nullable()->after('repeat_end');
            $table->json('excluded')->nullable()->after('recurrences');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['repeat_end', 'recurrences', 'excluded']);
        });
    }
};
