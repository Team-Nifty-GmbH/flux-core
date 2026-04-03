<?php

use FluxErp\Livewire\HumanResources\WorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class)
        ->assertOk();
});
