<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->boolean('is_billable')->after('description')->default(false);
        });

        // Join work time type and set is_billable to work_time_types.is_billable
        DB::statement('
            UPDATE work_times
            JOIN work_time_types ON work_time_types.id = work_times.work_time_type_id
            SET work_times.is_billable = work_time_types.is_billable
        ');
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropColumn('is_billable');
        });
    }
};
