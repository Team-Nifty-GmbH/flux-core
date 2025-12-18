<?php

use FluxErp\Livewire\Contact\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communication::class)
        ->assertOk();
});
