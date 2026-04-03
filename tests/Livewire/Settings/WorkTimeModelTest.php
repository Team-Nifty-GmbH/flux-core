<?php

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
