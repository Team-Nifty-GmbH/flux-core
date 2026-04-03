<?php

use FluxErp\Livewire\AbsenceRequest\Activities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertOk();
});
