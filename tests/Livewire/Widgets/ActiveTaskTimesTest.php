<?php

use FluxErp\Livewire\Widgets\ActiveTaskTimes;
use FluxErp\Models\WorkTime;
use Livewire\Livewire;

test('renders successfully', function (): void {
    WorkTime::factory()
        ->for($this->user)
        ->create([
            'is_daily_work_time' => false,
            'is_locked' => false,
        ]);

    Livewire::test(ActiveTaskTimes::class)
        ->assertOk()
        ->assertCount('items', 1);
});
