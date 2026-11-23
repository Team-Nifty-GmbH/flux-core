<?php

use Carbon\Carbon;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Settings\WorkTimeModel;
use FluxErp\Models\WorkTimeModel as WorkTimeModelModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $workTimeModel = app(WorkTimeModelModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    Livewire::test(WorkTimeModel::class, ['id' => $workTimeModel->getKey()])
        ->assertOk();
});

test('loads a sunday schedule stored as weekday 0 into the form', function (): void {
    $workTimeModel = app(WorkTimeModelModel::class)->create([
        'name' => 'Seven days',
        'weekly_hours' => 42,
        'work_days_per_week' => 7,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'has_fixed_hours' => true,
        'is_active' => true,
    ]);
    $workTimeModel->schedules()->create([
        'week_number' => 1,
        'weekday' => Carbon::SUNDAY,
        'work_hours' => 2,
    ]);

    Livewire::test(WorkTimeModel::class, ['id' => $workTimeModel->getKey()])
        ->assertSet('workTimeModelForm.schedules.0.days.7.weekday', Carbon::SUNDAY)
        ->assertSet('workTimeModelForm.schedules.0.days.7.work_hours', 2.0);
});
