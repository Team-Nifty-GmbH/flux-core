<?php

use FluxErp\Livewire\Employee\EmployeeBalanceAdjustments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmployeeBalanceAdjustments::class)
        ->assertOk();
});
