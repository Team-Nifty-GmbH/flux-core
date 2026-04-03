<?php

use FluxErp\Livewire\Employee\AbsenceRequests;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->assertOk();
});
