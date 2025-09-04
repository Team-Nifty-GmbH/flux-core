<?php

use FluxErp\Livewire\Address\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communication::class)
        ->assertOk();
});
