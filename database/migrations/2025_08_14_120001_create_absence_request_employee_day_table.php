<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_request_employee_day', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('absence_request_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('employee_day_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['absence_request_id', 'employee_day_id'], 'absence_request_employee_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_request_employee_day');
    }
};
