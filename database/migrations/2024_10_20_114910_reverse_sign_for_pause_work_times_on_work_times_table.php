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
            $table->bigInteger('total_time_ms')->default(0)->change();
        });

        DB::statement('UPDATE work_times SET total_time_ms = total_time_ms * -1 WHERE is_pause = true');
    }

    public function down(): void
    {
        DB::statement('UPDATE work_times SET total_time_ms = ABS(total_time_ms) WHERE is_pause = true');

        Schema::table('work_times', function (Blueprint $table): void {
            $table->unsignedBigInteger('total_time_ms')->default(0)->change();
        });
    }
};
