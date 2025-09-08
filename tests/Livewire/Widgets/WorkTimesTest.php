<?php

use FluxErp\Livewire\Widgets\WorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class)
        ->assertOk();
});
