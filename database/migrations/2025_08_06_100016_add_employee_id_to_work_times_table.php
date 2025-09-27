<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->foreignId('employee_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
