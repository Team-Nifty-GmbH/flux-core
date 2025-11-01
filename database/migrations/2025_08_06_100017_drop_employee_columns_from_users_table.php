<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'date_of_birth',
                'employee_number',
                'employment_date',
                'termination_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->date('date_of_birth')->after('color')->nullable();
            $table->string('employee_number')->after('date_of_birth')->nullable();
            $table->date('employment_date')->after('employee_number')->nullable();
            $table->date('termination_date')->after('employment_date')->nullable();
        });
    }
};
