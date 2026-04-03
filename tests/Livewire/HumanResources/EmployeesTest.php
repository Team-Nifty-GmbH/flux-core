<?php

use FluxErp\Livewire\HumanResources\Employees;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Employees::class)
        ->assertOk();
});
