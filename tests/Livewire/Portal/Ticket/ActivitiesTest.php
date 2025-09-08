<?php

use FluxErp\Livewire\Portal\Ticket\Activities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertOk();
});
