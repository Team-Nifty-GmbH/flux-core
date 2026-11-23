<?php

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;

test('create work time model', function (): void {
    $model = CreateWorkTimeModel::make([
        'name' => '40h Week',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
    ])->validate()->execute();

    expect($model)->name->toBe('40h Week');
});

test('create work time model requires name and hours', function (): void {
    CreateWorkTimeModel::assertValidationErrors([], ['name', 'weekly_hours']);
});

test('update work time model', function (): void {
    $model = CreateWorkTimeModel::make([
        'name' => '40h Week',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
    ])->validate()->execute();

    $updated = UpdateWorkTimeModel::make([
        'id' => $model->getKey(),
        'name' => '35h Week',
        'weekly_hours' => 35,
    ])->validate()->execute();

    expect($updated)->name->toBe('35h Week');
});

test('update work time model stores an empty break as zero', function (): void {
    $model = CreateWorkTimeModel::make([
        'name' => '40h Week',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'payment',
    ])->validate()->execute();

    $updated = UpdateWorkTimeModel::make([
        'id' => $model->getKey(),
        'schedules' => [
            [
                'week_number' => 1,
                'days' => [
                    [
                        'weekday' => 1,
                        'start_time' => '08:00',
                        'end_time' => '16:00',
                        'work_hours' => 8,
                        'break_minutes' => null,
                    ],
                ],
            ],
        ],
    ])->validate()->execute();

    expect($updated->schedules)->toHaveCount(1)
        ->and($updated->schedules->first()->break_minutes)->toBe(0);
});

test('delete work time model', function (): void {
    $model = CreateWorkTimeModel::make([
        'name' => 'Temp',
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'overtime_compensation' => 'none',
    ])->validate()->execute();

    expect(DeleteWorkTimeModel::make(['id' => $model->getKey()])
        ->validate()->execute())->toBeTrue();
});
