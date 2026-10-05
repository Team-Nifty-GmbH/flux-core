<?php

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\WorkTimeModel;

test('a work time model without fixed hours is created without fixed hour values', function (): void {
    $workTimeModel = CreateWorkTimeModel::make([
        'name' => 'Hourly',
        'cycle_weeks' => 3,
        'work_days_per_week' => 5,
        'max_overtime_hours' => 20,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff->value,
        'has_fixed_hours' => false,
    ])
        ->validate()
        ->execute();

    expect($workTimeModel->weekly_hours)->toEqual(0)
        ->and($workTimeModel->cycle_weeks)->toBe(1)
        ->and($workTimeModel->work_days_per_week)->toBeNull()
        ->and($workTimeModel->max_overtime_hours)->toBeNull();
});

test('a work time model with fixed hours still requires weekly hours', function (): void {
    CreateWorkTimeModel::make([
        'name' => 'Standard',
        'cycle_weeks' => 1,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff->value,
        'has_fixed_hours' => true,
    ])
        ->validate();
})->throws(Illuminate\Validation\ValidationException::class);

test('turning off fixed hours resets the fixed hour values', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'cycle_weeks' => 2,
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'max_overtime_hours' => 20,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $workTimeModel = UpdateWorkTimeModel::make([
        'id' => $workTimeModel->getKey(),
        'weekly_hours' => 40,
        'has_fixed_hours' => false,
    ])
        ->validate()
        ->execute();

    expect($workTimeModel->weekly_hours)->toEqual(0)
        ->and($workTimeModel->cycle_weeks)->toBe(1)
        ->and($workTimeModel->work_days_per_week)->toBeNull()
        ->and($workTimeModel->max_overtime_hours)->toBeNull();
});
