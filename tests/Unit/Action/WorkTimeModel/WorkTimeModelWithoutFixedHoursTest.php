<?php

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\WorkTimeModel;

test('a work time model without fixed hours is created without weekly hours', function (): void {
    $workTimeModel = CreateWorkTimeModel::make([
        'name' => 'Hourly',
        'cycle_weeks' => 1,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff->value,
        'has_fixed_hours' => false,
    ])
        ->validate()
        ->execute();

    expect($workTimeModel->weekly_hours)->toEqual(0);
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

test('turning off fixed hours resets the weekly hours', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
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

    expect($workTimeModel->weekly_hours)->toEqual(0);
});
