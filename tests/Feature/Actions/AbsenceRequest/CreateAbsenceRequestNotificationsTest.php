<?php

use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\EmployeeDepartment\CreateEmployeeDepartment;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestCreatedNotification;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteAssignedNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();

    $this->wtm = CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
        'is_active' => true,
    ])->validate()->execute();

    $this->absenceType = CreateAbsenceType::make([
        'name' => 'Other Leave',
        'code' => 'OTH',
        'color' => '#FF0000',
        'percentage_deduction' => 1.0,
        'employee_can_create' => 'yes',
        'affects_vacation' => false,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
    ])->validate()->execute();
});

test('creating notifies the department manager', function (): void {
    $managerUser = User::factory()->create();
    $manager = CreateEmployee::make([
        'firstname' => 'Manager',
        'lastname' => 'One',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $managerUser->getKey(),
    ])->validate()->execute();

    $department = CreateEmployeeDepartment::make([
        'name' => 'Test Department',
        'manager_employee_id' => $manager->getKey(),
    ])->validate()->execute();

    $employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'employee_department_id' => $department->getKey(),
    ])->validate()->execute();

    CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    Notification::assertSentTo($managerUser, AbsenceRequestCreatedNotification::class);
});

test('creating notifies each initial substitute', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $substituteUser = User::factory()->create();
    $substitute = CreateEmployee::make([
        'firstname' => 'Sub',
        'lastname' => 'Stitute',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $substituteUser->getKey(),
    ])->validate()->execute();

    CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
        'substitutes' => [$substitute->getKey()],
    ])->checkPermission()->validate()->execute();

    Notification::assertSentTo($substituteUser, AbsenceRequestSubstituteAssignedNotification::class);
});

test('creating does not notify a manager who has no user', function (): void {
    $manager = CreateEmployee::make([
        'firstname' => 'No',
        'lastname' => 'User',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $department = CreateEmployeeDepartment::make([
        'name' => 'Userless Dept',
        'manager_employee_id' => $manager->getKey(),
    ])->validate()->execute();

    $employee = CreateEmployee::make([
        'firstname' => 'Emp',
        'lastname' => 'Loyee',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'employee_department_id' => $department->getKey(),
    ])->validate()->execute();

    CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    Notification::assertNothingSent();
});

test('creating does not notify the auth user even if they are the manager', function (): void {
    $managerUser = User::factory()->create();
    $manager = CreateEmployee::make([
        'firstname' => 'Self',
        'lastname' => 'Manager',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $managerUser->getKey(),
    ])->validate()->execute();

    $department = CreateEmployeeDepartment::make([
        'name' => 'Self Managed Dept',
        'manager_employee_id' => $manager->getKey(),
    ])->validate()->execute();

    $employee = CreateEmployee::make([
        'firstname' => 'Team',
        'lastname' => 'Member',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'employee_department_id' => $department->getKey(),
    ])->validate()->execute();

    $this->actingAs($managerUser);

    CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    Notification::assertNotSentTo($managerUser, AbsenceRequestCreatedNotification::class);
});
