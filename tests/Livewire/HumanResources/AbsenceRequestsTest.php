<?php

use FluxErp\Livewire\HumanResources\AbsenceRequests;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->assertOk();
});
