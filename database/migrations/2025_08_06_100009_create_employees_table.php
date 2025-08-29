<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->uuid();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('country_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('location_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('supervisor_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->foreignId('employee_department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('salutation')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('confession')->nullable();

            $table->string('street')->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();

            $table->string('phone')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('email')->nullable();

            $table->string('social_security_number')->nullable();
            $table->string('tax_identification_number')->nullable();

            $table->string('employee_number')->nullable();
            $table->string('job_title')->nullable();
            $table->date('employment_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->date('probation_period_until')->nullable();
            $table->date('fixed_term_contract_until')->nullable();
            $table->date('work_permit_until')->nullable();
            $table->date('residence_permit_until')->nullable();
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->string('salary_type')->nullable();
            $table->string('payment_interval')->nullable();
            $table->decimal('hourly_rate')->nullable();

            $table->string('health_insurance')->nullable();
            $table->string('health_insurance_member_number')->nullable();

            $table->string('iban')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();

            $table->integer('number_of_children')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index('user_id');
            $table->index('client_id');
            $table->index('is_active');
            $table->index(['firstname', 'lastname']);
            $table->index('name');
            $table->index('email');
            $table->index('employment_date');
            $table->index('termination_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
