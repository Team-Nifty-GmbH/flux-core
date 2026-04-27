<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\UpcomingBirthdaysWidget;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(UpcomingBirthdaysWidget::class)
        ->assertOk();
});

test('shows employees with birthdays within next 30 days', function (): void {
    $birthdayDate = now()->addDays(10);

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Birthday',
        'lastname' => 'Person',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'date_of_birth' => $birthdayDate->copy()->subYears(30),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::withoutLazyLoading()
        ->test(UpcomingBirthdaysWidget::class)
        ->assertOk();

    $birthdays = $component->get('birthdays');

    expect($birthdays)->toHaveCount(1)
        ->and($birthdays[0]['name'])->toContain('Birthday')
        ->and((int) $birthdays[0]['age'])->toBe(30);
});

test('excludes employees with birthdays beyond 30 days', function (): void {
    $farBirthday = now()->addDays(45);

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Far',
        'lastname' => 'Birthday',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'date_of_birth' => $farBirthday->copy()->subYears(25),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    $component = Livewire::withoutLazyLoading()
        ->test(UpcomingBirthdaysWidget::class)
        ->assertOk();

    expect($component->get('birthdays'))->toBeEmpty();
});
