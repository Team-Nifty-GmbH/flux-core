<?php

use FluxErp\Livewire\MyEmployeeProfile\AbsenceRequests;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->assertOk();
});
