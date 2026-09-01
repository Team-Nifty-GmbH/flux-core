<?php

use FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RevokeAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestRevokedNotification;
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

test('revoking notifies the requesting employees user', function (): void {
    $employeeUser = User::factory()->create();

    $employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $employeeUser->getKey(),
    ])->validate()->execute();

    $absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    ApproveAbsenceRequest::make(['id' => $absenceRequest->getKey()])
        ->checkPermission()->validate()->execute();

    Notification::fake();

    RevokeAbsenceRequest::make(['id' => $absenceRequest->getKey()])
        ->checkPermission()->validate()->execute();

    Notification::assertSentTo($employeeUser, AbsenceRequestRevokedNotification::class);
});

test('revoking skips notification when the employee has no user', function (): void {
    $employee = CreateEmployee::make([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    ApproveAbsenceRequest::make(['id' => $absenceRequest->getKey()])
        ->checkPermission()->validate()->execute();

    Notification::fake();

    RevokeAbsenceRequest::make(['id' => $absenceRequest->getKey()])
        ->checkPermission()->validate()->execute();

    Notification::assertNothingSent();
});
