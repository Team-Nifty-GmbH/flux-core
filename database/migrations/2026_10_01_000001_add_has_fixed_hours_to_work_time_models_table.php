<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('work_time_models', function (Blueprint $table): void {
            $table->boolean('has_fixed_hours')->default(true)->after('overtime_compensation');
        });
    }

    public function down(): void
    {
        Schema::table('work_time_models', function (Blueprint $table): void {
            $table->dropColumn('has_fixed_hours');
        });
    }
};
