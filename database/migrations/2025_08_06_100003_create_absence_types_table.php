<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_types', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->string('code');
            $table->string('color', 7);
            $table->decimal('percentage_deduction', 3, 2)->default(1.00);
            $table->string('employee_can_create_enum');

            $table->boolean('affects_overtime')->default(false);
            $table->boolean('affects_sick_leave')->default(false);
            $table->boolean('affects_vacation')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_types');
    }
};
