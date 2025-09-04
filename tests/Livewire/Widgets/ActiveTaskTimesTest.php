<?php

use FluxErp\Livewire\Widgets\ActiveTaskTimes;
use FluxErp\Models\WorkTime;
use Livewire\Livewire;

beforeEach(function (): void {
    WorkTime::factory()
        ->for($this->user)
        ->create([
            'is_daily_work_time' => false,
            'is_locked' => false,
        ]);
});

test('renders successfully', function (): void {
    Livewire::test(ActiveTaskTimes::class)
        ->assertStatus(200)
        ->assertCount('items', 1);
});
