<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('color')->after('email')->nullable();
            $table->date('date_of_birth')->after('color')->nullable();
            $table->string('employee_number')->after('date_of_birth')->nullable();
            $table->unsignedInteger('vacation_days')->after('employee_number')->nullable();
            $table->decimal('daily_work_time', 40, 10)->after('vacation_days')->nullable();
            $table->date('employment_date')->after('vacation_days')->nullable();
            $table->date('termination_date')->after('employment_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'color',
                'date_of_birth',
                'employee_number',
                'vacation_days',
                'employment_date',
                'termination_date',
            ]);
        });
    }
};
