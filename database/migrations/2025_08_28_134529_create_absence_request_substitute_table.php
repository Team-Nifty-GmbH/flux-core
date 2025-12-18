<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_request_substitute', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('absence_request_id')
                ->constrained('absence_requests')
                ->cascadeOnDelete();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->unique(['absence_request_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_request_substitute');
    }
};
