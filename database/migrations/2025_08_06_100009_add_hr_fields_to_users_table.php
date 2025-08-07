<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->date('birth_date')->nullable()->after('employee_number');
            $table->string('social_security_number')->nullable()->after('birth_date');
            $table->string('tax_id')->nullable()->after('social_security_number');
            $table->string('tax_class')->nullable()->after('tax_id');
            $table->decimal('salary', 10, 2)->nullable()->after('tax_class');
            $table->enum('salary_type', ['hourly', 'monthly', 'yearly'])->nullable()->after('salary');
            $table->foreignId('work_time_model_id')->nullable()->after('salary_type')->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('work_time_model_id')->constrained()->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->after('location_id')->constrained('users')->nullOnDelete();
            $table->decimal('vacation_days_current', 5, 2)->default(0)->after('supervisor_id');
            $table->decimal('vacation_days_carried', 5, 2)->default(0)->after('vacation_days_current');
            $table->decimal('overtime_hours', 6, 2)->default(0)->after('vacation_days_carried');
            $table->string('emergency_contact_name')->nullable()->after('overtime_hours');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_phone');

            $table->index('employee_number');
            $table->index('work_time_model_id');
            $table->index('location_id');
            $table->index('supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['work_time_model_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['supervisor_id']);

            $table->dropColumn([
                'birth_date',
                'social_security_number',
                'tax_id',
                'tax_class',
                'salary',
                'salary_type',
                'work_time_model_id',
                'location_id',
                'supervisor_id',
                'vacation_days_current',
                'vacation_days_carried',
                'overtime_hours',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
            ]);
        });
    }
};
