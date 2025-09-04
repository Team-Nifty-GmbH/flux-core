<?php

use FluxErp\Livewire\Widgets\ActiveDailyWorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActiveDailyWorkTimes::class)
        ->assertOk();
});
