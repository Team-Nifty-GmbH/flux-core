<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->foreignId('manager_employee_id')
                ->nullable()
                ->after('location_id')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->dropForeign(['manager_employee_id']);
            $table->dropColumn('manager_employee_id');
        });
    }
};
