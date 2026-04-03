<?php

use FluxErp\Livewire\EmployeeDay\WorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class)
        ->assertOk();
});
