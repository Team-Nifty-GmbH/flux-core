<?php

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\User;
use FluxErp\Models\WorkTimeModel;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permission = Permission::findOrCreate(
        'api.widgets.overtime-balance-box.get',
        'sanctum'
    );

    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);
});

test('widget api returns the computed result for the given parameters', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    app(EmployeeDay::class)->create([
        'employee_id' => $this->employee->getKey(),
        'date' => now()->subDays(2),
        'target_hours' => 8.00,
        'actual_hours' => 9.50,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => 1.5,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    app(EmployeeDay::class)->create([
        'employee_id' => $this->employee->getKey(),
        'date' => now()->subDays(1),
        'target_hours' => 8.00,
        'actual_hours' => 7.50,
        'sick_days_used' => 0,
        'sick_hours_used' => 0,
        'vacation_days_used' => 0,
        'vacation_hours_used' => 0,
        'plus_minus_overtime_hours' => -0.5,
        'plus_minus_absence_hours' => 0,
        'is_work_day' => true,
        'is_holiday' => false,
        'break_minutes' => 30,
    ]);

    app(EmployeeBalanceAdjustment::class)->create([
        'employee_id' => $this->employee->getKey(),
        'type' => EmployeeBalanceAdjustmentTypeEnum::Overtime->value,
        'reason' => EmployeeBalanceAdjustmentReasonEnum::Correction,
        'amount' => 2,
        'effective_date' => now()->subDay()->format('Y-m-d'),
    ]);

    $response = $this->getJson(
        '/api/widgets/overtime-balance-box?employeeId=' . $this->employee->getKey()
    );

    $response->assertOk();

    expect($response->json('data.overtimeHours'))->toEqual(3.0)
        ->and($response->json('data.sum'))->toContain('3');
});

test('widget api validates the request parameters', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/overtime-balance-box');

    $response->assertUnprocessable()
        ->assertJsonValidationErrorFor('employeeId');
});

test('widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $response = $this->getJson('/api/widgets/overtime-balance-box');

    $response->assertForbidden();
});
