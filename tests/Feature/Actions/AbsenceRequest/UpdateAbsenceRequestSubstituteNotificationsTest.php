<?php

use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\UpdateAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteAssignedNotification;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteUnassignedNotification;
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

    $this->employee = CreateEmployee::make([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
    ])->validate()->execute();

    $keptUser = User::factory()->create();
    $this->keptEmployee = CreateEmployee::make([
        'firstname' => 'Kept',
        'lastname' => 'Sub',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $keptUser->getKey(),
    ])->validate()->execute();
    $this->keptUser = $keptUser;

    $addedUser = User::factory()->create();
    $this->addedEmployee = CreateEmployee::make([
        'firstname' => 'Added',
        'lastname' => 'Sub',
        'employment_date' => '2020-01-01',
        'tenant_id' => $this->dbTenant->getKey(),
        'work_time_model_id' => $this->wtm->getKey(),
        'user_id' => $addedUser->getKey(),
    ])->validate()->execute();
    $this->addedUser = $addedUser;

    $this->absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->checkPermission()->validate()->execute();

    $this->absenceRequest->substitutes()->attach($this->keptEmployee->getKey());

    Notification::fake();
});

test('update notifies only newly added substitutes', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'substitutes' => [$this->keptEmployee->getKey(), $this->addedEmployee->getKey()],
    ])->checkPermission()->validate()->execute();

    Notification::assertSentTo($this->addedUser, AbsenceRequestSubstituteAssignedNotification::class);
    Notification::assertNotSentTo($this->keptUser, AbsenceRequestSubstituteAssignedNotification::class);
});

test('update notifies removed substitutes', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'substitutes' => [],
    ])->checkPermission()->validate()->execute();

    Notification::assertSentTo($this->keptUser, AbsenceRequestSubstituteUnassignedNotification::class);
});

test('update without a substitutes key sends no substitute notifications', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'reason' => 'unrelated update',
    ])->checkPermission()->validate()->execute();

    Notification::assertNothingSent();
});
