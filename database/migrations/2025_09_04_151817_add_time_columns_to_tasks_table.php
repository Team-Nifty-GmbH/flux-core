<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('due_time')->nullable()->after('due_date');
            $table->dateTime('start_datetime')->nullable()->after('due_time');
            $table->dateTime('due_datetime')->nullable()->after('start_datetime');
        });

        DB::table('tasks')
            ->update([
                'start_datetime' => DB::raw(
                    "CASE
                        WHEN start_date IS NOT NULL THEN CONCAT(start_date, ' 00:00:00')
                        ELSE NULL
                    END"
                ),
                'due_datetime' => DB::raw(
                    "CASE
                        WHEN due_date IS NOT NULL THEN CONCAT(due_date, ' 23:59:59')
                        ELSE NULL
                    END"
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn([
                'start_time',
                'due_time',
                'start_datetime',
                'due_datetime',
            ]);
        });
    }
};
