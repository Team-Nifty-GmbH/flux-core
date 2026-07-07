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
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
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

    $this->absenceRequest = CreateAbsenceRequest::make([
        'employee_id' => $this->employee->getKey(),
        'absence_type_id' => $this->absenceType->getKey(),
        'state' => AbsenceRequestStateEnum::Pending->value,
        'day_part' => AbsenceRequestDayPartEnum::FullDay,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-05',
    ])->checkPermission()->validate()->execute();

    $this->absenceRequest->substitutes()->attach($this->keptEmployee->getKey());

    Notification::fake();
});

test('changing the period re-notifies kept substitutes', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'start_date' => '2026-06-08',
        'end_date' => '2026-06-12',
        'substitutes' => [$this->keptEmployee->getKey()],
    ])->checkPermission()->validate()->execute();

    Notification::assertSentTo($this->keptUser, AbsenceRequestSubstituteAssignedNotification::class);
});

test('updating only an unrelated field does not re-notify kept substitutes', function (): void {
    UpdateAbsenceRequest::make([
        'id' => $this->absenceRequest->getKey(),
        'reason' => 'unrelated update',
        'substitutes' => [$this->keptEmployee->getKey()],
    ])->checkPermission()->validate()->execute();

    Notification::assertNothingSent();
});
