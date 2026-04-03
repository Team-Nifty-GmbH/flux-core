<?php

use FluxErp\Livewire\EmployeeDay\Activities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertOk();
});
