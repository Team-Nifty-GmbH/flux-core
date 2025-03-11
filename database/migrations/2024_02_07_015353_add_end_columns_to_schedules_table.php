<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->dateTime('ends_at')->nullable()->after('due_at');
            $table->unsignedInteger('recurrences')->nullable()->after('ends_at');
            $table->unsignedInteger('current_recurrence')->nullable()->after('recurrences');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropColumn([
                'ends_at',
                'recurrences',
                'current_recurrence',
            ]);
        });
    }
};
